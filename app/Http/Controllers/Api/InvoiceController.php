<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /*
    |--------------------------------------------------------------------------
    | USER ENDPOINTS
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'invoice_code', 'start_date', 'end_date', 'search']);
        $perPage = (int) $request->query('per_page', 15);
        $invoices = $this->invoiceService->getInvoices($filters, $request->user(), $perPage);

        return InvoiceResource::collection($invoices);
    }

    public function show(Request $request, $id)
    {
        try {
            $invoice = $this->invoiceService->getInvoiceById((int) $id, $request->user());
            return new InvoiceResource($invoice);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Invoice not found or unauthorized.'], 404);
        }
    }

    public function preview(Request $request, $id)
    {
        try {
            $invoice = $this->invoiceService->getInvoiceById((int) $id, $request->user());
            $html = $this->invoiceService->renderInvoiceHtml($invoice);
            return response($html, 200)->header('Content-Type', 'text/html');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Invoice not found or unauthorized.'], 404);
        }
    }

    public function exportPdf(Request $request, $id)
    {
        try {
            $invoice = $this->invoiceService->getInvoiceById((int) $id, $request->user());
            return $this->invoiceService->generateInvoicePdf($invoice);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Invoice not found or unauthorized.'], 404);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN ENDPOINTS
    |--------------------------------------------------------------------------
    */

    public function adminIndex(Request $request)
    {
        $filters = $request->only(['status', 'invoice_code', 'start_date', 'end_date', 'search']);
        $perPage = (int) $request->query('per_page', 20);
        $invoices = $this->invoiceService->getInvoices($filters, null, $perPage);

        return InvoiceResource::collection($invoices);
    }

    public function adminShow(Request $request, $id)
    {
        try {
            $invoice = $this->invoiceService->getInvoiceById((int) $id);
            return new InvoiceResource($invoice);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        }
    }

    public function adminPreview(Request $request, $id)
    {
        try {
            $invoice = $this->invoiceService->getInvoiceById((int) $id);
            $html = $this->invoiceService->renderInvoiceHtml($invoice);
            return response($html, 200)->header('Content-Type', 'text/html');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        }
    }

    public function adminExportPdf(Request $request, $id)
    {
        try {
            $invoice = $this->invoiceService->getInvoiceById((int) $id);
            return $this->invoiceService->generateInvoicePdf($invoice);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        }
    }
}
