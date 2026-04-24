<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppSender
{
    public function __construct(protected AppSettings $settings) {}

    public function sendText(string $to, string $text): void
    {
        $this->send([
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $text],
        ]);
    }

    public function sendButtons(string $to, string $bodyText, array $buttons): void
    {
        $formattedButtons = collect($buttons)->map(fn (array $btn) => [
            'type' => 'reply',
            'reply' => [
                'id' => $btn['id'],
                'title' => mb_substr($btn['title'], 0, 20),
            ],
        ])->toArray();

        $this->send([
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => $bodyText],
                'action' => [
                    'buttons' => $formattedButtons,
                ],
            ],
        ]);
    }

    private function send(array $payload): void
    {
        $version = $this->settings->get('whatsapp.graph_version')
            ?? config('whatsapp.api_version');
        $phoneId = $this->settings->get('whatsapp.phone_number_id')
            ?? config('whatsapp.phone_number_id');
        $url = "https://graph.facebook.com/{$version}/{$phoneId}/messages";

        $token = $this->settings->get('whatsapp.access_token')
            ?? config('whatsapp.api_token');

        $response = Http::withToken($token)
            ->post($url, $payload);

        if (! $response->successful()) {
            Log::error('WhatsApp send failed', [
                'to' => $payload['to'] ?? 'unknown',
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}
