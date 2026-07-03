<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaywayService
{
    protected string $merchantId;
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->merchantId = config('services.payway.merchant_id');
        $this->apiKey = config('services.payway.api_key');
        $this->baseUrl = config('services.payway.base_url'); // sandbox or production
    }

    /**
     * Check transaction status from PayWay
     * https://docs.payway.com.kh -> Check Transaction API
     */
    public function checkTransaction(string $tranId): array
    {
        $reqTime = now()->format('YmdHis');

        $hashString = $reqTime . $this->merchantId . $tranId;
        $hash = base64_encode(hash_hmac('sha512', $hashString, $this->apiKey, true));

        $response = Http::asForm()->post("{$this->baseUrl}/api/payment-gateway/v1/payments/check-transaction-2", [
            'req_time' => $reqTime,
            'merchant_id' => $this->merchantId,
            'tran_id' => $tranId,
            'hash' => $hash,
        ]);

        $result = $response->json();

        Log::info('PayWay check-transaction', [
            'tran_id' => $tranId,
            'response' => $result,
        ]);

        return $result;
    }

    /**
     * Helper: return true only if payment is actually successful
     */
    public function isTransactionSuccessful(string $tranId): bool
    {
        $result = $this->checkTransaction($tranId);

        // PayWay returns status.code === "00" សម្រាប់ success
        // payment_status: "APPROVED" ជា status ដែលបញ្ជាក់ថាបានបង់ប្រាក់
        return isset($result['status']['code'])
            && $result['status']['code'] === '00'
            && isset($result['payment_status'])
            && $result['payment_status'] === 'APPROVED';
    }
}
