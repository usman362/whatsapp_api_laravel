<?php

namespace App\Http\Controllers;

use App\Models\WaProcessedMessage;
use App\Services\AttendanceOrchestrator;
use App\Services\AppSettings;
use App\Services\UserResolver;
use App\Services\WhatsAppSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        protected UserResolver $userResolver,
        protected AttendanceOrchestrator $orchestrator,
        protected WhatsAppSender $sender,
        protected AppSettings $settings,
    ) {}

    /**
     * GET — Meta webhook verification (hub.challenge).
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $expectedToken = $this->settings->get('whatsapp.verify_token')
            ?? config('whatsapp.verify_token');

        if ($mode === 'subscribe' && $token === $expectedToken) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /**
     * POST — Incoming webhook events from WhatsApp Cloud API.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::channel('whatsapp')->info('Webhook received', ['payload' => $payload]);

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? '') !== 'messages') {
                    continue;
                }

                foreach ($change['value']['messages'] ?? [] as $message) {
                    $this->processMessage($message);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function processMessage(array $message): void
    {
        $messageId = $message['id'] ?? null;
        $from = $message['from'] ?? null;
        $type = $message['type'] ?? null;

        if (! $messageId || ! $from) {
            return;
        }

        if (WaProcessedMessage::where('provider_message_id', $messageId)->exists()) {
            Log::channel('whatsapp')->info('Duplicate message skipped', ['message_id' => $messageId]);

            return;
        }

        WaProcessedMessage::create(['provider_message_id' => $messageId]);

        $user = $this->userResolver->resolve($from);

        if (! $user) {
            $this->sender->sendText(
                $from,
                'Tu número no está registrado. Contacta con administración de tu empresa o llama al (+34) 87245 41 42.'
            );

            return;
        }

        Log::channel('whatsapp')->info('Processing message', [
            'phone' => $user->phone_e164,
            'type' => $type,
            'api_base_url' => $user->api_base_url,
        ]);

        if ($type === 'interactive') {
            $buttonId = $message['interactive']['button_reply']['id'] ?? null;

            if ($buttonId) {
                $this->orchestrator->handleButtonPress($user, $buttonId);
            }
        } else {
            $messageText = $message['text']['body'] ?? '';
            $this->orchestrator->handleMessage($user, $messageText);
        }
    }
}
