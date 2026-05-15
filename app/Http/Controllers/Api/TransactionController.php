<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionHistoryResource;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Services\DuitkuService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function checkStatus($invoiceCode)
    {
        $getTransaction = Transaction::where("invoice_code", $invoiceCode)->first();
        if (!$getTransaction) {
            return response()->json(["message" => "Transaction not found"], 404);
        }
        return response()->json(["transaction_status" => $getTransaction->transaction_status], 200);
    }

    private function generateMerchantOrderId(): string
    {
        return 'SYN-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
    }

    /*
    |--------------------------------------------------------------------------
    | USER: List own transactions
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $transactions = Transaction::where("user_id", $request->user()->id)->with(['plan','payment'])->latest()->get();

        return TransactionHistoryResource::collection($transactions);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: List ALL transactions (with search, filter, pagination)
    |--------------------------------------------------------------------------
    */
    public function adminIndex(Request $request)
    {
        $query = Transaction::with(['user', 'plan', 'payment']);

        // Search by invoice code or user name/email
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($status = $request->query('status')) {
            $query->where('transaction_status', $status);
        }

        $perPage = $request->query('per_page', 20);
        $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    public function show($id)
    {
        $transaction = Transaction::with([
            'user',
            'plan',
            'payment',
        ])->findOrFail($id);

        return response()->json([
            'message' => 'Detail transaksi berhasil diambil.',
            'data' => $transaction
        ]);
    }


    public function store(Request $request, DuitkuService $duitku)
    {
        $user = $request->user();
        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'payment_method' => ['nullable', 'string'],
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $paymentAmount = (int) round((float) $plan->price) + mt_rand(1, 999);
        $paymentMethod = Payment::where("payment_code", $request->payment_method)->first();
        if (!$paymentMethod) {
            return response()->json([
                'message' => 'Payment method tidak valid.',
            ], 400);
        }
        $merchantOrderId = $this->generateMerchantOrderId();
        $expiryPeriod = (int) config('services.duitku.expiry_period', 10);

        try {
            $duitkuPayload = [
                'merchantCode' => $duitku->merchantCode(),
                'paymentAmount' => $paymentAmount,
                'paymentMethod' => $paymentMethod->payment_code,
                'merchantOrderId' => $merchantOrderId,
                'productDetails' => 'Pembayaran paket ' . $plan->name . ' - ' . $plan->description,
                'customerVaName' => 'Synthera User',
                'email' => (string) $user->email,
                'itemDetails' => [
                    [
                        'name' => $plan->name,
                        'price' => $paymentAmount,
                        'quantity' => 1,
                    ],
                ],
                'signature' => $duitku->createInquirySignature($merchantOrderId, $paymentAmount),
                'expiryPeriod' => $expiryPeriod,
            ];
            $duitkuResponse = $duitku->createInquiry($duitkuPayload);
            $statusCode = (string) ($duitkuResponse['statusCode'] ?? '');
            if ($statusCode !== '00') {
                return response()->json([
                    'message' => 'Gagal membuat pembayaran ke Duitku.',
                    'duitku' => $duitkuResponse,
                ], 422);
            }
            $qrisUrl = $duitku->generateQr($duitkuResponse['qrString'], $merchantOrderId);

            Transaction::create([
                'invoice_code' => $duitkuPayload["merchantOrderId"],
                'user_id' => $user->id,
                'payment_id' => $paymentMethod->id,
                'plan_id' => $plan->id,
                'amount' => $duitkuPayload["paymentAmount"],
                'final_amount' => $duitkuPayload["paymentAmount"],
                'transaction_status' => "pending",
                'discount_amount' => 0,
                'notes' => $duitkuPayload["productDetails"],
                'CreatedBy' => $user->name,
                'CreatedDate' => now(),
            ]);

            return response()->json([
                'message' => 'Transaction berhasil dibuat.',
                'data' => [
                    'invoice_code' => $duitkuPayload['merchantOrderId'],
                    'payment_method' => $duitkuPayload['paymentMethod'],
                    'amount' => $duitkuPayload['paymentAmount'],
                    'payment_url' => $qrisUrl,
                ],
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses transaksi.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'transaction_status' => 'required|string'
        ]);

        $transaction->update([
            'transaction_status' => $validated['transaction_status'],
            'LastUpdateBy' => $request->user()->name ?? 'Synthera',
            'LastUpdateDate' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status transaksi berhasil diperbarui.',
            'data' => $transaction->fresh()->load(['user', 'plan', 'payment'])
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaksi tidak ditemukan.'
            ], 404);
        }

        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dihapus.'
        ]);
    }
}
