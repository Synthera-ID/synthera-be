<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function index()
    {
        return Payment::all();
    }

    public function show($id)
    {
        return Payment::findOrFail($id);
    }

    // ✅ CREATE PAYMENT (QRIS DUITKU)
    public function create(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'user_id' => 'required|exists:users,id',
            'payment_method' => 'required',
            'amount' => 'required|numeric',
        ]);

        $merchantCode = env('DUITKU_MERCHANT_CODE');
        $apiKey = env('DUITKU_API_KEY');

        $merchantOrderId = 'INV-' . time();
        $amount = (int) $request->amount;

        // 🔐 signature untuk create
        $signature = md5($merchantCode . $merchantOrderId . $amount . $apiKey);

        // 💾 simpan ke DB
        $payment = Payment::create([
            'transaction_id' => $request->transaction_id,
            'user_id' => $request->user_id,
            'payment_method' => $request->payment_method,
            'payment_gateway' => 'duitku',
            'gateway_ref' => $merchantOrderId,
            'amount' => $amount,
            'payment_status' => 'pending',
            'paid_at' => null,

            'CompanyCode' => 'SYNTHERA',
            'Status' => 1,
            'IsDeleted' => 0,
            'CreatedBy' => 'system',
            'CreatedDate' => now(),
        ]);

        // 📡 request ke Duitku (QRIS)
        $duitkuResponse = Http::post(
            'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry',
            [
                'merchantCode' => $merchantCode,
                'paymentAmount' => $amount,
                'paymentMethod' => 'SP', // QRIS
                'merchantOrderId' => $merchantOrderId,
                'productDetails' => 'Payment Synthera',
                'email' => 'test@gmail.com',
                'callbackUrl' => env('DUITKU_CALLBACK_URL'),
                'returnUrl' => env('DUITKU_RETURN_URL'),
                'signature' => $signature
            ]
        );

        $duitku = $duitkuResponse->json();

        // ❗ HANDLE kalau Duitku error
        if (!isset($duitku['statusCode']) || $duitku['statusCode'] != '00') {
            return response()->json([
                'message' => 'Gagal membuat pembayaran',
                'duitku_response' => $duitku
            ], 400);
        }

        return response()->json([
            'message' => 'Payment created',
            'order_id' => $merchantOrderId,
            'amount' => $amount,
            'payment_url' => $duitku['paymentUrl'] ?? null,
            'qr_string' => $duitku['qrString'] ?? null
        ]);
    }

    // ✅ CALLBACK (UPDATE STATUS)
    public function callback(Request $request)
    {
        $merchantCode = env('DUITKU_MERCHANT_CODE');
        $apiKey = env('DUITKU_API_KEY');

        $merchantOrderId = $request->merchantOrderId;
        $amount = $request->amount;
        $signature = $request->signature;

        // 🔐 VALIDASI SIGNATURE (WAJIB DI PRODUCTION)
        $validSignature = md5($merchantCode . $amount . $merchantOrderId . $apiKey);

       if ($signature != $validSignature) {
    return response()->json([
        'message' => 'Invalid signature'
    ], 400);
}

        // 🔍 cari payment
        $payment = Payment::where('gateway_ref', $merchantOrderId)->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Payment tidak ditemukan'
            ], 404);
        }

        // 🔁 update status dari Duitku
        if ($request->resultCode == '00') {
            $payment->payment_status = 'success';
            $payment->paid_at = now();
        } else {
            $payment->payment_status = 'failed';
        }

        $payment->save();

        return response()->json([
            'message' => 'Callback success',
            'payment' => $payment
        ]);
    }
}