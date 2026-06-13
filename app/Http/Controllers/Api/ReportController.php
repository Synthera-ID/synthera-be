<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function preview(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date']);
        $reportData = $this->reportService->getReportData($filters);

        return response()->json([
            'success' => true,
            'data' => $reportData,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date']);
        $reportData = $this->reportService->getReportData($filters);

        return $this->reportService->generateReportPdf($reportData);
    }

    public function exportCsv(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date']);

        return $this->reportService->generateReportCsv($filters);
    }
}
