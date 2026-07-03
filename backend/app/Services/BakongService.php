<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;

class BakongService
{
    protected string $baseUrl;
    protected string $token;
    protected string $bakongAccountID;
    protected string $merchantName;
    protected string $merchantCity;

    public function __construct()
    {
        $this->baseUrl = env('BAKONG_API_URL'); // https://api-bakong.nbc.gov.kh
        $this->token = env('BAKONG_TOKEN');   // Bearer JWT token ពី Bakong Developer portal
        $this->bakongAccountID = env('BAKONG_ACCOUNT_ID', 'sovanna_man@bkrt');
        $this->merchantName = env('BAKONG_MERCHANT_NAME', 'SOVANNA MAN');
        $this->merchantCity = env('BAKONG_MERCHANT_CITY', 'PHNOM PENH');
    }

    /**
     * Generate individual KHQR string + md5 for a given amount
     * $amount ត្រូវជា decimal ធម្មតា (ឧ. 25000 សម្រាប់ KHR, 12.50 សម្រាប់ USD)
     * $currency: KHQRData::CURRENCY_KHR ឬ KHQRData::CURRENCY_USD
     */


    public function generateQr(
        float $amount,
        string $currency = null,
        ?string $billNumber = null,
        ?int $expiresInSeconds = 300
    ): array {
        $currency ??= (string) KHQRData::CURRENCY_KHR;

        $expirationTimestamp = (string) (
            floor(microtime(true) * 1000)
            + ($expiresInSeconds * 1000)
        );

        $individualInfo = new IndividualInfo(
            bakongAccountID: $this->bakongAccountID,
            merchantName: $this->merchantName,
            merchantCity: $this->merchantCity,
            currency: $currency,
            amount: $amount,
            billNumber: $billNumber,
            expirationTimestamp: $expirationTimestamp,
        );

        $result = BakongKHQR::generateIndividual($individualInfo);

        if ($result->status['code'] !== 0) {
            throw new \RuntimeException(
                'Failed to generate KHQR: ' . $result->status['message']
            );
        }

        return [
            'qr' => $result->data['qr'],
            'md5' => $result->data['md5'],
            'expires_at' => $expirationTimestamp,
            'expires_in' => $expiresInSeconds,
        ];
    }


    public function generateDeepLink(string $qr): string
    {
        // Fix 1: Ensure app name doesn't contain local file characters or spaces that trip validations
        $appName = 'MyTestStore';

        $response = Http::withToken($this->token)
            ->acceptJson()
            ->post("{$this->baseUrl}/v1/generate_deeplink_by_qr", [
                'qr' => $qr,
                'sourceInfo' => [
                    // Fix 2: Use a real public image domain instead of localhost
                    'appIconUrl' => 'https://bakong.nbc.gov.kh/images/logo.svg',
                    'appName' => $appName,
                    // Fix 3: Use a dummy public URL format instead of your local URL
                    'appDeepLinkCallback' => 'https://bakong.nbc.gov.kh/',
                ],
            ]);

        if ($response->failed() || $response->json('responseCode') !== 0) {
            throw new \Exception("Bakong Deeplink Generation Failed: " . $response->json('responseMessage'));
        }

        return $response->json('data.shortLink') ?? '';
    }
    /**
     * Check transaction by MD5 hash of KHQR string
     */
    public function checkTransactionByMd5(string $md5): array
    {
        $response = Http::withToken($this->token)
            ->acceptJson()
            ->post("{$this->baseUrl}/v1/check_transaction_by_md5", [
                'md5' => $md5,
            ]);

        $result = $response->json();

        Log::info('Bakong check-transaction', [
            'md5' => $md5,
            'response' => $result,
        ]);

        return $result;
    }

    public function isTransactionSuccessful(string $md5): bool
    {
        $result = $this->checkTransactionByMd5($md5);

        return isset($result['responseCode']) && $result['responseCode'] === 0;
    }
}
