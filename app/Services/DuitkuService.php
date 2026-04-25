<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class DuitkuService
{
    public function merchantCode()
    {
        return config('services.duitku.merchant_code');
    }

    public function apiKey()
    {
        return config('services.duitku.api_key');
    }

    private function baseUrl(): string
    {
        $environment = strtolower((string) config('services.duitku.env', 'sandbox'));
        $isProduction = $environment === 'production' || (bool) config('services.duitku.is_production', false);

        return $isProduction
            ? 'https://passport.duitku.com'
            : 'https://sandbox.duitku.com';
    }

    private function timeout(): int
    {
        return max(1, (int) config('services.duitku.timeout', 30));
    }

    // Menghitung signature untuk request ke Duitku
    public function createInquirySignature($merchantOrderId, $amount)
    {
        return md5($this->merchantCode() . $merchantOrderId . $amount . $this->apiKey());
    }

    // Validasi signature yang dikirim Duitku ke Callback kita
    public function validateCallbackSignature($merchantCode, $amount, $merchantOrderId, $signature)
    {
        $calcSignature = md5($merchantCode . $amount . $merchantOrderId . $this->apiKey());
        return $signature === $calcSignature;
    }

    // Fungsi untuk menembak API Duitku (Inquiry)
    public function createInquiry(array $payload)
    {
        $url = $this->baseUrl() . '/webapi/api/merchant/v2/inquiry';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
            ->timeout($this->timeout())
            ->post($url, $payload);

        if (! $response->successful()) {
            throw new RuntimeException(
                sprintf('Duitku inquiry gagal. HTTP %d: %s', $response->status(), $response->body())
            );
        }

        return $response->json() ?? [];
    }

    // Fungsi untuk cek status transaksi secara manual
    public function checkTransactionStatus($merchantOrderId)
    {
        $signature = md5($this->merchantCode() . $merchantOrderId . $this->apiKey());
        
        $payload = [
            'merchantCode' => $this->merchantCode(),
            'merchantOrderId' => $merchantOrderId,
            'signature' => $signature
        ];

        $url = $this->baseUrl() . '/webapi/api/merchant/transactionStatus';
        $response = Http::timeout($this->timeout())->post($url, $payload);

        if (! $response->successful()) {
            throw new RuntimeException(
                sprintf('Duitku status check gagal. HTTP %d: %s', $response->status(), $response->body())
            );
        }

        return $response->json() ?? [];
    }
}
