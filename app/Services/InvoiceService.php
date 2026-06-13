<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class InvoiceService
{
    /**
     * Get invoices with filters and pagination.
     */
    public function getInvoices(array $filters = [], ?User $user = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Invoice::query()->with(['user', 'transaction.plan', 'transaction.payment']);

        if ($user) {
            $query->where('user_id', $user->id);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['invoice_code']) && $filters['invoice_code'] !== '') {
            $query->where('invoice_code', 'like', '%' . $filters['invoice_code'] . '%');
        }

        if (isset($filters['start_date']) && $filters['start_date'] !== '') {
            $query->whereDate('issued_at', '>=', Carbon::parse($filters['start_date']));
        }

        if (isset($filters['end_date']) && $filters['end_date'] !== '') {
            $query->whereDate('issued_at', '<=', Carbon::parse($filters['end_date']));
        }

        if (isset($filters['search']) && $filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('invoice_code', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function (Builder $uq) use ($search) {
                        $uq->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query->orderBy('issued_at', 'desc')->paginate($perPage);
    }

    /**
     * Get single invoice detail by ID, checking owner if user is provided.
     */
    public function getInvoiceById(int $id, ?User $user = null): Invoice
    {
        $query = Invoice::query()->with(['user', 'transaction.plan', 'transaction.payment']);

        if ($user) {
            $query->where('user_id', $user->id);
        }

        return $query->findOrFail($id);
    }

    /**
     * Generate or update an invoice from a transaction.
     */
    public function generateInvoiceFromTransaction(Transaction $transaction): Invoice
    {
        $invoice = Invoice::where('transaction_id', $transaction->id)->first() ?? new Invoice();

        $invoice->transaction_id = $transaction->id;
        $invoice->user_id = $transaction->user_id;
        $invoice->invoice_code = $transaction->invoice_code;
        $invoice->amount = $transaction->amount;
        $invoice->discount_amount = $transaction->discount_amount;
        $invoice->tax_amount = 0;
        $invoice->total_amount = $transaction->final_amount;
        
        $statusMap = [
            'pending' => 'pending',
            'paid' => 'paid',
            'completed' => 'paid',
            'failed' => 'failed',
            'refunded' => 'cancelled',
        ];
        $invoice->status = $statusMap[$transaction->transaction_status] ?? 'pending';

        if (!$invoice->exists) {
            $invoice->issued_at = now();
            $invoice->due_at = now()->addDays(3);
            $invoice->CreatedBy = $transaction->CreatedBy ?? 'System';
            $invoice->CreatedDate = now();
        }

        if ($transaction->transaction_status === 'paid' || $transaction->transaction_status === 'completed') {
            $invoice->paid_at = now();
        }

        $invoice->notes = $transaction->notes;
        $invoice->CompanyCode = $transaction->CompanyCode;
        $invoice->LastUpdateBy = $transaction->LastUpdateBy ?? 'System';
        $invoice->LastUpdateDate = now();

        $invoice->save();

        return $invoice->load(['user', 'transaction.plan', 'transaction.payment']);
    }

    /**
     * Render the invoice as an HTML preview string.
     */
    public function renderInvoiceHtml(Invoice $invoice): string
    {
        return view('pdf.invoice', ['invoice' => $invoice])->render();
    }

    /**
     * Generate PDF stream for the invoice.
     */
    public function generateInvoicePdf(Invoice $invoice): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);
        return $pdf->download('invoice_' . $invoice->invoice_code . '.pdf');
    }
}
