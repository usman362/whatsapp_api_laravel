<?php

namespace App\Services;

use App\Models\WaUser;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CompanyApiClient
{
    private const DEFAULT_TIMEOUT = 10;

    public function __construct(protected AppSettings $settings) {}

    public function lastStatus(WaUser $user): ?array
    {
        return $this->request('GET', $user, '/last_status', [
            'phone' => $user->phone_e164,
        ]);
    }

    public function checkIn(WaUser $user): ?array
    {
        return $this->request('POST', $user, '/check_in', [
            'phone' => $user->phone_e164,
        ]);
    }

    public function checkOut(WaUser $user): ?array
    {
        return $this->request('POST', $user, '/check_out', [
            'phone' => $user->phone_e164,
        ]);
    }

    public function workedTime(WaUser $user): ?array
    {
        return $this->request('GET', $user, '/worked_time', [
            'phone' => $user->phone_e164,
        ]);
    }

    private function request(string $method, WaUser $user, string $path, array $params): ?array
    {
        $url = rtrim($user->api_base_url, '/') . $path;
        $context = [
            'phone' => $user->phone_e164,
            'api_base_url' => $user->api_base_url,
            'endpoint' => $path,
        ];

        try {
            $client = $this->client($user);
            $response = match (strtoupper($method)) {
                'POST' => $client->post($url, $params),
                default => $client->get($url, $params),
            };

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('CompanyAPI request failed', $context + [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('CompanyAPI request exception', $context + [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function client(WaUser $user): PendingRequest
    {
        $timeout = (int) ($this->settings->get('company_api.timeout')
            ?? config('services.company_api.timeout', self::DEFAULT_TIMEOUT));
        $client = Http::timeout($timeout)->acceptJson();

        if ($user->api_token) {
            $client = $client->withToken($user->api_token);
        }

        return $client;
    }
}
