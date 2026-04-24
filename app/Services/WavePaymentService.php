<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentProvider;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WavePaymentService
{
    protected ?string $apiKey;

    protected ?string $webhookSecret;

    protected string $baseUrl = 'https://api.wave.com';

    protected bool $enabled;

    public function __construct()
    {
        $provider = PaymentProvider::getBySlug('wave');

        $this->enabled = $provider?->is_enabled ?? false;
        $this->apiKey = $provider?->api_key;
        $this->webhookSecret = $provider?->webhook_secret;
    }

    public function isEnabled(): bool
    {
        return $this->enabled && ! empty($this->apiKey);
    }

    /**
     * Créer une session de paiement Wave Checkout
     *
     * @throws Exception
     */
    public function createCheckoutSession(Order $order, string $successUrl, string $errorUrl): array
    {
        if (! $this->isEnabled()) {
            throw new Exception("Le module de paiement Wave n'est pas activé ou configuré.");
        }

        $payload = [
            'amount' => (string) intval($order->total),
            'currency' => 'XOF',
            'client_reference' => $order->order_number,
            'success_url' => $successUrl,
            'error_url' => $errorUrl,
        ];

        Log::info('Wave Checkout Session Creation:', [
            'client_reference' => $payload['client_reference'],
            'amount' => $payload['amount'],
            'currency' => $payload['currency'],
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post("{$this->baseUrl}/v1/checkout/sessions", $payload);

        if ($response->successful()) {
            $data = $response->json();
            Log::info('Wave Checkout Session Created:', [
                'client_reference' => $payload['client_reference'],
                'session_id' => $data['id'] ?? null,
            ]);

            return $data;
        }

        Log::error('Wave Checkout Error:', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        throw new Exception('Erreur lors de la création de la session Wave: '.$response->body());
    }

    /**
     * Récupérer une session de checkout par ID
     */
    public function getCheckoutSession(string $sessionId): ?array
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
        ])->get("{$this->baseUrl}/v1/checkout/sessions/{$sessionId}");

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Wave Get Session Error:', [
            'session_id' => $sessionId,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Rechercher une session par référence client (order_number)
     */
    public function searchByClientReference(string $clientReference): ?array
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
        ])->get("{$this->baseUrl}/v1/checkout/sessions/search", [
            'client_reference' => $clientReference,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            return $data['result'][0] ?? null;
        }

        return null;
    }

    /**
     * Vérifier la signature du webhook (Signing Secret strategy)
     */
    public function verifyWebhookSignature(string $signature, string $rawBody): bool
    {
        if (empty($this->webhookSecret)) {
            Log::warning('Wave webhook secret not configured');

            return false;
        }

        $parts = explode(',', $signature);
        $timestamp = null;
        $signatures = [];

        foreach ($parts as $part) {
            [$prefix, $value] = explode('=', $part, 2);
            if ($prefix === 't') {
                $timestamp = $value;
            } elseif ($prefix === 'v1') {
                $signatures[] = $value;
            }
        }

        if (! $timestamp || empty($signatures)) {
            Log::warning('Wave webhook: Invalid signature format');

            return false;
        }

        $computedHmac = hash_hmac('sha256', $timestamp.$rawBody, $this->webhookSecret);

        $valid = in_array($computedHmac, $signatures, true);

        if (! $valid) {
            Log::warning('Wave webhook: Signature mismatch', [
                'expected' => $computedHmac,
                'received' => $signatures,
            ]);
        }

        return $valid;
    }

    /**
     * Vérification simple avec shared secret (alternative)
     */
    public function verifySharedSecret(string $authHeader): bool
    {
        if (empty($this->webhookSecret)) {
            return false;
        }

        $expectedHeader = 'Bearer '.$this->webhookSecret;

        return hash_equals($expectedHeader, $authHeader);
    }
}
