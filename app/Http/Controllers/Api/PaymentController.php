<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DuitkuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaymentController extends Controller
{

    public function index(Request $request)
    {
        return PaymentResource::collection(
            Payment::latest()->get()
        );
    }

    public function show($id)
    {
        return new PaymentResource(Payment::findOrFail($id));
    }

    
    public function callback(Request $request, DuitkuService $duitku)
    {
        $validated = $request->validate([
            'merchantCode' => ['required', 'string'],
            'amount' => ['required'],
            'merchantOrderId' => ['required', 'string'],
            'signature' => ['required', 'string'],
            'resultCode' => ['nullable', 'string'],
            'paymentCode' => ['nullable', 'string'],
            'reference' => ['nullable', 'string'],
            'publisherOrderId' => ['nullable', 'string'],
            'spUserHash' => ['nullable', 'string'],
            'settlementDate' => ['nullable', 'string'],
            'issuerCode' => ['nullable', 'string'],
        ]);

        $merchantCode = (string) $validated['merchantCode'];
        $amount = (string) $validated['amount'];
        $merchantOrderId = (string) $validated['merchantOrderId'];
        $signature = (string) $validated['signature'];
        $resultCode = (string) ($validated['resultCode'] ?? '');

        if ($merchantCode !== $duitku->merchantCode()) {
            return response()->json([
                'message' => 'Invalid merchant code.',
            ], 400);
        }

        if (!$duitku->validateCallbackSignature($merchantCode, $amount, $merchantOrderId, $signature)) {
            return response()->json([
                'message' => 'Invalid callback signature.',
            ], 400);
        }

        $transaction = Transaction::where('invoice_code', $merchantOrderId)->first();

        if (!$transaction) {
            Log::warning('Callback Duitku order tidak ditemukan', [
                'merchant_order_id' => $merchantOrderId,
                'reference' => $validated['reference'] ?? null,
            ]);
            return response()->json([
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }
        $statusTrx = $resultCode === '00' ? 'completed' : 'failed';
        $transaction->update([
            'transaction_status' => $statusTrx,
        ]);
        if ($resultCode === '00') {
            Membership::updateOrCreate(
                [
                    'user_id' => $transaction->user_id,
                ],
                [
                    'plan_id' => $transaction->plan_id,
                    'membership_status' => 'active',
                    'start_date' => now(),
                    'end_date' => now()->addMonth(),
                ]
            );
        }

        return response()->json([
            'message' => 'Pembayaran Berhasil',
        ]);
    }



}
