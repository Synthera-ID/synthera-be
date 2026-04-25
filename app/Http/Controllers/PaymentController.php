<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DuitkuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentController extends Controller
{
    private const QRIS_METHODS = ['SP', 'NQ', 'GQ', 'SQ'];

    public function index()
    {
        return Payment::all();
    }

    public function show($id)
    {
        return Payment::findOrFail($id);
    }

    public function postPayment(Request $request, DuitkuService $duitku)
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'payment_method' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'customer_va_name' => ['nullable', 'string', 'max:100'],
            'merchant_user_info' => ['nullable', 'string', 'max:255'],
            'additional_param' => ['nullable', 'string', 'max:255'],
            'callback_url' => ['nullable', 'url', 'max:255'],
            'return_url' => ['nullable', 'url', 'max:255'],
            'expiry_period' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ]);

        $user = $this->resolveUser($request, $validated['user_id'] ?? null);
        if (! $user) {
            return response()->json([
                'message' => 'User tidak ditemukan. Gunakan token auth atau kirim user_id.',
            ], 422);
        }

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $paymentAmount = (int) round((float) $plan->price);

        if ($paymentAmount < 10000) {
            return response()->json([
                'message' => 'Nominal minimum Duitku adalah Rp10.000.',
            ], 422);
        }

        $paymentMethod = strtoupper((string) ($validated['payment_method'] ?? config('services.duitku.qris_payment_method', 'SP')));
        if (! in_array($paymentMethod, self::QRIS_METHODS, true)) {
            return response()->json([
                'message' => 'payment_method QRIS tidak valid. Gunakan SP, NQ, GQ, atau SQ.',
            ], 422);
        }

        $merchantOrderId = $this->generateMerchantOrderId();
        $callbackUrl = (string) ($validated['callback_url'] ?? config('services.duitku.callback_url', ''));
        if ($callbackUrl === '') {
            $callbackUrl = rtrim((string) config('app.url'), '/') . '/api/payment/callback';
        }

        $returnUrl = (string) ($validated['return_url'] ?? config('services.duitku.return_url', config('app.url')));
        $expiryPeriod = (int) ($validated['expiry_period'] ?? config('services.duitku.expiry_period', 10));

        try {
            return DB::transaction(function () use (
                $validated,
                $user,
                $plan,
                $paymentAmount,
                $paymentMethod,
                $merchantOrderId,
                $callbackUrl,
                $returnUrl,
                $expiryPeriod,
                $duitku
            ) {
                $transaction = Transaction::create([
                    'invoice_code' => $merchantOrderId,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'discount_id' => null,
                    'amount' => $paymentAmount,
                    'discount_amount' => 0,
                    'final_amount' => $paymentAmount,
                    'transaction_status' => 'pending',
                    'notes' => 'Pembayaran QRIS melalui Duitku.',
                ]);

                $duitkuPayload = [
                    'merchantCode' => $duitku->merchantCode(),
                    'paymentAmount' => $paymentAmount,
                    'paymentMethod' => $paymentMethod,
                    'merchantOrderId' => $merchantOrderId,
                    'productDetails' => 'Pembayaran paket ' . $plan->name,
                    'additionalParam' => (string) ($validated['additional_param'] ?? ''),
                    'merchantUserInfo' => (string) ($validated['merchant_user_info'] ?? ''),
                    'customerVaName' => (string) ($validated['customer_va_name'] ?? $user->name ?? 'Synthera User'),
                    'email' => (string) ($validated['email'] ?? $user->email),
                    'phoneNumber' => (string) ($validated['phone_number'] ?? $user->phone ?? ''),
                    'itemDetails' => [
                        [
                            'name' => $plan->name,
                            'price' => $paymentAmount,
                            'quantity' => 1,
                        ],
                    ],
                    'callbackUrl' => $callbackUrl,
                    'returnUrl' => $returnUrl,
                    'signature' => $duitku->createInquirySignature($merchantOrderId, $paymentAmount),
                    'expiryPeriod' => $expiryPeriod,
                ];

                $duitkuResponse = $duitku->createInquiry($duitkuPayload);
                $statusCode = (string) ($duitkuResponse['statusCode'] ?? '');

                if ($statusCode !== '00') {
                    $transaction->update([
                        'transaction_status' => 'failed',
                        'notes' => 'Duitku inquiry gagal: ' . ($duitkuResponse['statusMessage'] ?? 'Unknown error'),
                    ]);

                    return response()->json([
                        'message' => 'Gagal membuat pembayaran ke Duitku.',
                        'duitku' => $duitkuResponse,
                    ], 422);
                }

                Payment::updateOrCreate(
                    ['transaction_id' => $transaction->id],
                    [
                        'user_id' => $user->id,
                        'payment_method' => 'e_wallet',
                        'payment_gateway' => 'duitku',
                        'gateway_ref' => $duitkuResponse['reference'] ?? null,
                        'amount' => $paymentAmount,
                        'payment_status' => 'pending',
                        'paid_at' => now(),
                    ]
                );

                return response()->json([
                    'message' => 'Payment berhasil dibuat.',
                    'data' => [
                        'merchant_order_id' => $merchantOrderId,
                        'transaction_id' => $transaction->id,
                        'payment_method' => $paymentMethod,
                        'payment_url' => $duitkuResponse['paymentUrl'] ?? null,
                        'app_url' => $duitkuResponse['appUrl'] ?? null,
                        'va_number' => $duitkuResponse['vaNumber'] ?? null,
                        'qr_string' => $duitkuResponse['qrString'] ?? null,
                        'reference' => $duitkuResponse['reference'] ?? null,
                        'amount' => (int) ($duitkuResponse['amount'] ?? $paymentAmount),
                        'status_code' => $statusCode,
                        'status_message' => $duitkuResponse['statusMessage'] ?? null,
                    ],
                ], 201);
            });
        } catch (RuntimeException $e) {
            Log::error('Duitku post payment gagal', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Terjadi kendala saat menghubungi Duitku.',
                'error' => $e->getMessage(),
            ], 502);
        } catch (\Throwable $e) {
            Log::error('Unexpected error saat post payment Duitku', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Terjadi error saat membuat pembayaran.',
            ], 500);
        }
    }

    public function getPayment(string $merchantOrderId, DuitkuService $duitku)
    {
        $transaction = Transaction::where('invoice_code', $merchantOrderId)->first();
        if (! $transaction) {
            return response()->json([
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        try {
            $duitkuStatus = $duitku->checkTransactionStatus($merchantOrderId);
            $statusCode = (string) ($duitkuStatus['statusCode'] ?? '01');
            [$transactionStatus, $paymentStatus] = $this->mapDuitkuStatus($statusCode);

            DB::transaction(function () use ($transaction, $duitkuStatus, $transactionStatus, $paymentStatus) {
                $transaction->update([
                    'transaction_status' => $transactionStatus,
                    'notes' => 'Duitku status check: ' . ($duitkuStatus['statusMessage'] ?? ''),
                ]);

                $currentPayment = Payment::where('transaction_id', $transaction->id)->first();

                Payment::updateOrCreate(
                    ['transaction_id' => $transaction->id],
                    [
                        'user_id' => $transaction->user_id,
                        'payment_method' => 'e_wallet',
                        'payment_gateway' => 'duitku',
                        'gateway_ref' => $duitkuStatus['reference'] ?? $currentPayment?->gateway_ref,
                        'amount' => (int) ($duitkuStatus['amount'] ?? $transaction->final_amount),
                        'payment_status' => $paymentStatus,
                        'paid_at' => $paymentStatus === 'success'
                            ? now()
                            : ($currentPayment?->paid_at ?? now()),
                    ]
                );
            });

            $transaction->refresh();
            $payment = Payment::where('transaction_id', $transaction->id)->first();

            return response()->json([
                'message' => 'Status payment berhasil diambil.',
                'data' => [
                    'merchant_order_id' => $merchantOrderId,
                    'transaction_status' => $transaction->transaction_status,
                    'payment_status' => $payment?->payment_status,
                    'reference' => $duitkuStatus['reference'] ?? null,
                    'amount' => $duitkuStatus['amount'] ?? null,
                    'fee' => $duitkuStatus['fee'] ?? null,
                    'status_code' => $duitkuStatus['statusCode'] ?? null,
                    'status_message' => $duitkuStatus['statusMessage'] ?? null,
                ],
            ]);
        } catch (RuntimeException $e) {
            Log::error('Duitku get payment gagal', [
                'merchant_order_id' => $merchantOrderId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Terjadi kendala saat cek status ke Duitku.',
                'error' => $e->getMessage(),
            ], 502);
        } catch (\Throwable $e) {
            Log::error('Unexpected error saat get payment Duitku', [
                'merchant_order_id' => $merchantOrderId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Terjadi error saat mengambil status pembayaran.',
            ], 500);
        }
    }

    public function callback(Request $request, DuitkuService $duitku)
    {
        Log::info('Duitku Callback Masuk:', [
        'data' => $request->all(),
        'ip'   => $request->ip()
        ]);


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

        if (! $duitku->validateCallbackSignature($merchantCode, $amount, $merchantOrderId, $signature)) {
            return response()->json([
                'message' => 'Invalid callback signature.',
            ], 400);
        }

        $transaction = Transaction::where('invoice_code', $merchantOrderId)->first();
        if (! $transaction) {
            Log::warning('Callback Duitku order tidak ditemukan', [
                'merchant_order_id' => $merchantOrderId,
                'reference' => $validated['reference'] ?? null,
            ]);

            return response()->json([
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        $statusCode = $resultCode === '00' ? '00' : '02';

        try {
            $statusCheck = $duitku->checkTransactionStatus($merchantOrderId);
            if (! empty($statusCheck['statusCode'])) {
                $statusCode = (string) $statusCheck['statusCode'];
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal check status Duitku saat callback, fallback ke resultCode.', [
                'merchant_order_id' => $merchantOrderId,
                'message' => $e->getMessage(),
            ]);
        }

        [$transactionStatus, $paymentStatus] = $this->mapDuitkuStatus($statusCode);

        DB::transaction(function () use (
            $transaction,
            $amount,
            $validated,
            $transactionStatus,
            $paymentStatus
        ) {
            $transaction->update([
                'transaction_status' => $transactionStatus,
                'notes' => sprintf(
                    'Callback Duitku resultCode=%s reference=%s',
                    (string) ($validated['resultCode'] ?? ''),
                    (string) ($validated['reference'] ?? '')
                ),
            ]);

            $currentPayment = Payment::where('transaction_id', $transaction->id)->first();

            Payment::updateOrCreate(
                ['transaction_id' => $transaction->id],
                [
                    'user_id' => $transaction->user_id,
                    'payment_method' => 'e_wallet',
                    'payment_gateway' => 'duitku',
                    'gateway_ref' => $validated['reference'] ?? $currentPayment?->gateway_ref,
                    'amount' => (int) $amount,
                    'payment_status' => $paymentStatus,
                    'paid_at' => $paymentStatus === 'success'
                        ? now()
                        : ($currentPayment?->paid_at ?? now()),
                ]
            );
        });

        return response()->json([
            'message' => 'OK',
        ]);
    }

    private function resolveUser(Request $request, ?int $userId): ?User
    {
        $authUser = $request->user();
        if ($authUser) {
            return $authUser;
        }

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }

    private function generateMerchantOrderId(): string
    {
        return 'SYN-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
    }

    private function mapDuitkuStatus(string $statusCode): array
    {
        return match ($statusCode) {
            '00' => ['paid', 'success'],
            '02' => ['failed', 'failed'],
            default => ['pending', 'pending'],
        };
    }
}
