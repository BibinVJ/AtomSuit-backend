<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class BMLPaymentService
{
    const BML_SANDBOX_ENDPOINT = 'https://api.uat.merchants.bankofmaldives.com.mv/public/';
    const BML_PRODUCTION_ENDPOINT = 'https://api.merchants.bankofmaldives.com.mv/public/';

    protected string $baseUrl;
    // protected string $appId;
    protected string $apiKey;
    protected string $callbackUrl;
    protected string $returnUrl;


    public function __construct()
    {
        $this->baseUrl = (config('services.bml.environment') === 'production' ? self::BML_PRODUCTION_ENDPOINT : self::BML_SANDBOX_ENDPOINT);
        // $this->appId = config('services.bml.app_id');
        $this->apiKey = config('services.bml.api_key');
        $this->callbackUrl = config('services.bml.callback_url');
        $this->returnUrl = config('services.bml.return_url');
    }

    protected function headers(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $this->apiKey
        ];
    }

    protected function sendRequest(string $method, string $uri, array $payload = []): array
    {
        $response = Http::withHeaders($this->headers())
            ->{$method}("{$this->baseUrl}{$uri}", $payload);

        if ($response->failed()) {
            throw new Exception("BML API Error ({$uri}): " . $response->body(), $response->status());
        }

        return $response->json();
    }

    public function createTransaction(array $payload): array
    {
        return $this->sendRequest('post', '/transactions', $payload);
    }

    public function getTransaction(string $transactionId): array
    {
        return $this->sendRequest('get', "/transactions/{$transactionId}");
    }

}