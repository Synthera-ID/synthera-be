<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SubscriptionPlan;
use App\Models\Payment;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ReportService
{
    /**
     * Get aggregated report data based on filters.
     */
    public function getReportData(array $filters = []): array
    {
        $startDate = isset($filters['start_date']) ? Carbon::parse($filters['start_date'])->startOfDay() : now()->subDays(30)->startOfDay();
        $endDate = isset($filters['end_date']) ? Carbon::parse($filters['end_date'])->endOfDay() : now()->endOfDay();

        // 1. Summary Stats
        $summary = [
            'total_revenue' => (float) Invoice::where('status', 'paid')
                ->whereBetween('issued_at', [$startDate, $endDate])
                ->sum('total_amount'),
            'total_invoices' => Invoice::whereBetween('issued_at', [$startDate, $endDate])->count(),
            'paid_invoices' => Invoice::where('status', 'paid')
                ->whereBetween('issued_at', [$startDate, $endDate])
                ->count(),
            'pending_invoices' => Invoice::where('status', 'pending')
                ->whereBetween('issued_at', [$startDate, $endDate])
                ->count(),
            'failed_invoices' => Invoice::where('status', 'failed')
                ->whereBetween('issued_at', [$startDate, $endDate])
                ->count(),
        ];

        // 2. Plan Distribution
        $planDistribution = Invoice::whereBetween('invoices.issued_at', [$startDate, $endDate])
            ->join('transactions', 'invoices.transaction_id', '=', 'transactions.id')
            ->join('subscription_plans', 'transactions.plan_id', '=', 'subscription_plans.id')
            ->select('subscription_plans.name', DB::raw('count(invoices.id) as count'), DB::raw('sum(invoices.total_amount) as revenue'))
            ->groupBy('subscription_plans.id', 'subscription_plans.name')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'count' => (int) $item->count,
                    'revenue' => (float) $item->revenue,
                ];
            })
            ->toArray();

        // 3. Payment Method Distribution
        $paymentDistribution = Invoice::whereBetween('invoices.issued_at', [$startDate, $endDate])
            ->join('transactions', 'invoices.transaction_id', '=', 'transactions.id')
            ->join('payments', 'transactions.payment_id', '=', 'payments.id')
            ->select('payments.payment_method', DB::raw('count(invoices.id) as count'), DB::raw('sum(invoices.total_amount) as revenue'))
            ->groupBy('payments.payment_method')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->payment_method,
                    'count' => (int) $item->count,
                    'revenue' => (float) $item->revenue,
                ];
            })
            ->toArray();

        // 4. Monthly Trend (group by YYYY-MM)
        $isPostgres = DB::connection()->getDriverName() === 'pgsql';
        $monthGroupExpression = $isPostgres 
            ? "to_char(invoices.issued_at, 'YYYY-MM')" 
            : "strftime('%Y-%m', invoices.issued_at)";

        $monthlyTrend = Invoice::whereBetween('invoices.issued_at', [$startDate, $endDate])
            ->select(DB::raw("$monthGroupExpression as month"), DB::raw('count(invoices.id) as count'), DB::raw('sum(invoices.total_amount) as revenue'))
            ->groupBy(DB::raw($monthGroupExpression))
            ->orderBy(DB::raw($monthGroupExpression), 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month,
                    'count' => (int) $item->count,
                    'revenue' => (float) $item->revenue,
                ];
            })
            ->toArray();

        return [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'summary' => $summary,
            'plan_distribution' => $planDistribution,
            'payment_method_distribution' => $paymentDistribution,
            'monthly_trends' => $monthlyTrend,
        ];
    }

    /**
     * Get invoice list mapping for CSV exports.
     */
    public function getInvoiceListForExport(array $filters = []): array
    {
        $startDate = isset($filters['start_date']) ? Carbon::parse($filters['start_date'])->startOfDay() : now()->subDays(30)->startOfDay();
        $endDate = isset($filters['end_date']) ? Carbon::parse($filters['end_date'])->endOfDay() : now()->endOfDay();

        return Invoice::with(['user', 'transaction.plan', 'transaction.payment'])
            ->whereBetween('issued_at', [$startDate, $endDate])
            ->orderBy('issued_at', 'desc')
            ->get()
            ->map(function ($invoice) {
                return [
                    'invoice_code' => $invoice->invoice_code,
                    'user_name' => $invoice->user->name ?? '',
                    'user_email' => $invoice->user->email ?? '',
                    'plan_name' => $invoice->transaction->plan->name ?? '',
                    'payment_method' => $invoice->transaction->payment->payment_method ?? '',
                    'amount' => (float) $invoice->amount,
                    'discount_amount' => (float) $invoice->discount_amount,
                    'total_amount' => (float) $invoice->total_amount,
                    'status' => $invoice->status,
                    'issued_at' => $invoice->issued_at->toDateTimeString(),
                    'paid_at' => $invoice->paid_at ? $invoice->paid_at->toDateTimeString() : null,
                ];
            })
            ->toArray();
    }

    /**
     * Generate Report PDF response.
     */
    public function generateReportPdf(array $reportData): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView('pdf.report', $reportData);
        return $pdf->download('sales_report_' . $reportData['start_date'] . '_to_' . $reportData['end_date'] . '.pdf');
    }

    /**
     * Generate CSV file using Laravel Excel.
     */
    public function generateReportCsv(array $filters): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $data = $this->getInvoiceListForExport($filters);
        $export = new SalesReportExport($data);
        
        $startDate = isset($filters['start_date']) ? Carbon::parse($filters['start_date'])->toDateString() : now()->subDays(30)->toDateString();
        $endDate = isset($filters['end_date']) ? Carbon::parse($filters['end_date'])->toDateString() : now()->toDateString();
        
        return Excel::download($export, 'sales_report_' . $startDate . '_to_' . $endDate . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}
