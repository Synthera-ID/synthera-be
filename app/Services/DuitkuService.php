<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
        $url = config('services.duitku.env') === 'sandbox' 
            ? 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry'
            : 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        return $response->json();
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

        $url = 'https://sandbox.duitku.com/webapi/api/merchant/transactionStatus';
        $response = Http::post($url, $payload);

        return $response->json();
    }
}