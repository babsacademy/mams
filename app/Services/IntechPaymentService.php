<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentProvider;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IntechPaymentService
{
    protected ?string $apiKey;

    protected ?string $apiSecret;

    protected ?string $serviceId;

    protected string $baseUrl;

    protected bool $enabled;

    public function __construct()
    {
        $provider = PaymentProvider::getBySlug('intech');

        $this->enabled = $provider?->is_enabled ?? false;
        $environment = $provider?->environment ?? 'sandbox';

        $this->apiKey = $provider?->api_key;
        $this->apiSecret = $provider?->api_secret;
        $this->serviceId = $provider?->merchant_id;

        $this->baseUrl = $environment === 'production'
            ? 'https://api.intech.sn/api-services'
            : 'https://t-pay.intech.sn/api-services';
    }

    public function isEnabled(): bool
    {
        return $this->enabled && ! empty($this->apiKey) && ! empty($this->apiSecret);
    }

    /**
     * Initier un paiement
     *
     * @throws Exception
     */
    public function initiatePayment(Order $order, string $callbackUrl)
    {
        if (! $this->isEnabled()) {
            throw new Exception("Le module de paiement Intech n'est pas activé ou configuré.");
        }

        $phone = preg_replace('/[^\d]/', '', $order->customer_phone);
        if (strlen($phone) == 9) {
            $phone = '221'.$phone;
        }

        $payload = [
            'phone' => $phone,
            'amount' => (int) $order->total,
            'codeService' => $this->serviceId,
            'externalTransactionId' => $order->order_number,
            'callbackUrl' => $callbackUrl,
            'apiKey' => $this->apiKey,
        ];

        Log::info('Intech Payment Initiation:', [
            'order_number' => $order->order_number,
            'amount' => $payload['amount'],
            'url' => "{$this->baseUrl}/operation",
        ]);

        $response = Http::timeout(30)->post("{$this->baseUrl}/operation", $payload);

        if ($response->successful()) {
            $data = $response->json();
            Log::info('Intech Response Success:', [
                'order_number' => $order->order_number,
                'status' => $data['status'] ?? null,
                'transactionId' => $data['transactionId'] ?? null,
            ]);

            return $data;
        }

        Log::error('Intech Payment Error:', ['status' => $response->status(), 'body' => $response->body()]);
        throw new Exception("Erreur lors de l'initialisation du paiement: ".$response->body());
    }

    /**
     * Vérifier l'intégrité du callback via HMAC-SHA256
     */
    public function verifyCallback(array $data): bool
    {
        if (empty($this->apiSecret)) {
            Log::warning('Intech callback: apiSecret not configured, rejecting callback');

            return false;
        }

        $receivedSignature = $data['signature'] ?? null;

        if (empty($receivedSignature)) {
            Log::warning('Intech callback: missing signature field');

            return false;
        }

        $payload = collect($data)
            ->except('signature')
            ->sortKeys()
            ->implode('');

        $expectedSignature = hash_hmac('sha256', $payload, $this->apiSecret);

        $valid = hash_equals($expectedSignature, $receivedSignature);

        if (! $valid) {
            Log::warning('Intech callback: signature mismatch');
        }

        return $valid;
    }
}
