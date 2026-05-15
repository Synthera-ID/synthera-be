<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\DuitkuService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PUBLIC: GET ALL PAYMENTS (for checkout flow)
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Payment::where('IsDeleted', 0)->get()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC: GET DETAIL PAYMENT
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $payment = Payment::where('IsDeleted', 0)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: LIST ALL PAYMENT METHODS (with search & filter)
    |--------------------------------------------------------------------------
    */
    public function adminIndex(Request $request)
    {
        $query = Payment::where('IsDeleted', 0);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('payment_method', 'like', "%{$search}%")
                  ->orWhere('payment_code', 'like', "%{$search}%")
                  ->orWhere('payment_gateway', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('payment_status', $status);
        }

        if ($method = $request->query('method')) {
            $query->where('payment_method', $method);
        }

        $perPage = $request->query('per_page', 50);
        $payments = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payments->items(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: CREATE PAYMENT METHOD
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'payment_code' => 'required|string',
            'payment_gateway' => 'required|string',
            'min_amount' => 'required|numeric',
            'payment_status' => 'required|string',
        ]);

        $payment = Payment::create([
            'payment_method' => $request->payment_method,
            'payment_code' => $request->payment_code,
            'payment_gateway' => $request->payment_gateway,
            'min_amount' => $request->min_amount,
            'payment_status' => $request->payment_status,
            'CreatedBy' => $request->user()->name ?? 'Synthera',
            'CreatedDate' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method created successfully.',
            'data' => $payment
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: UPDATE PAYMENT METHOD
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $payment = Payment::where('IsDeleted', 0)->findOrFail($id);

        $request->validate([
            'payment_method' => 'sometimes|string',
            'payment_code' => 'sometimes|string',
            'payment_gateway' => 'sometimes|string',
            'min_amount' => 'sometimes|numeric',
            'payment_status' => 'sometimes|string',
        ]);

        $payment->update([
            'payment_method' => $request->payment_method ?? $payment->payment_method,
            'payment_code' => $request->payment_code ?? $payment->payment_code,
            'payment_gateway' => $request->payment_gateway ?? $payment->payment_gateway,
            'min_amount' => $request->min_amount ?? $payment->min_amount,
            'payment_status' => $request->payment_status ?? $payment->payment_status,
            'LastUpdateBy' => $request->user()->name ?? 'Synthera',
            'LastUpdateDate' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully.',
            'data' => $payment->fresh()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: DELETE PAYMENT METHOD (soft delete)
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, $id)
    {
        $payment = Payment::where('IsDeleted', 0)->findOrFail($id);

        $payment->update([
            'IsDeleted' => 1,
            'LastUpdateBy' => $request->user()->name ?? 'Synthera',
            'LastUpdateDate' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully.'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED: Post Payment (Duitku)
    |--------------------------------------------------------------------------
    */
    public function postPayment(Request $request, DuitkuService $duitku)
    {
        // existing postPayment logic
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC: Duitku Callback
    |--------------------------------------------------------------------------
    */
    public function callback(Request $request, DuitkuService $duitku)
    {
        $merchantCode = $request->merchantCode;
        $amount = $request->amount;
        $merchantOrderId = $request->merchantOrderId;
        $signature = $request->signature;

        if (!$duitku->validateCallbackSignature($merchantCode, $amount, $merchantOrderId, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $transaction = \App\Models\Transaction::where('invoice_code', $merchantOrderId)->first();
        if ($transaction) {
            $transaction->update([
                'transaction_status' => $request->resultCode === '00' ? 'paid' : 'failed',
                'LastUpdateBy' => 'Duitku Callback',
                'LastUpdateDate' => now(),
            ]);
        }

        return response()->json(['message' => 'Callback processed'], 200);
    }
}
