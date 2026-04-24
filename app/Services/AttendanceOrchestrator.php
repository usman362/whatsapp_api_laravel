<?php

namespace App\Services;

use App\Models\WaAttendanceLog;
use App\Models\WaUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceOrchestrator
{
    public function __construct(
        protected CompanyApiClient $companyApi,
        protected WhatsAppSender $sender,
    ) {}

    public function handleMessage(WaUser $user, string $messageText = ''): void
    {
        $greeting = $this->isGreeting($messageText)
            ? $this->buildGreeting($user)
            : null;

        $this->showCurrentStatus($user, $greeting);
    }

    private function isGreeting(string $text): bool
    {
        $greetings = ['hola', 'buenos días', 'buenos dias', 'buenas tardes', 'buenas noches', 'hey', 'hi', 'hello'];

        return in_array(mb_strtolower(trim($text)), $greetings, true);
    }

    private function buildGreeting(WaUser $user): string
    {
        $name = $user->name ? " {$user->name}" : '';
        $hour = (int) Carbon::now()->format('H');

        $saludo = match (true) {
            $hour < 12 => 'Buenos días',
            $hour < 20 => 'Buenas tardes',
            default => 'Buenas noches',
        };

        return "{$saludo}{$name}! 👋";
    }

    public function handleButtonPress(WaUser $user, string $buttonId): void
    {
        match ($buttonId) {
            'check_in' => $this->handleCheckIn($user),
            'check_out' => $this->handleCheckOut($user),
            'worked_info' => $this->handleWorkedInfo($user),
            'status_refresh' => $this->showCurrentStatus($user),
            default => $this->showCurrentStatus($user),
        };
    }

    private function showCurrentStatus(WaUser $user, ?string $greeting = null): void
    {
        $status = $this->companyApi->lastStatus($user);
        $prefix = $greeting ? "{$greeting}\n\n" : '';

        if ($status === null) {
            $this->sender->sendButtons(
                $user->phone_e164,
                "{$prefix}No he podido consultar tu estado ahora. Inténtalo más tarde.",
                [['id' => 'status_refresh', 'title' => 'Reintentar']]
            );

            return;
        }

        $lastStatus = strtoupper($status['status'] ?? 'OUT');

        if ($lastStatus === 'IN') {
            $this->sender->sendButtons(
                $user->phone_e164,
                "{$prefix}Estás actualmente fichado. ¿Quieres salir?",
                [
                    ['id' => 'check_out', 'title' => 'Salir'],
                    ['id' => 'worked_info', 'title' => 'Tiempo trabajado'],
                ]
            );
        } else {
            $this->sender->sendButtons(
                $user->phone_e164,
                "{$prefix}¿Quieres entrar?",
                [
                    ['id' => 'check_in', 'title' => 'Entrar'],
                    ['id' => 'worked_info', 'title' => 'Tiempo trabajado'],
                ]
            );
        }
    }

    private function handleCheckIn(WaUser $user): void
    {
        $now = Carbon::now();
        $result = $this->companyApi->checkIn($user);

        if ($result !== null) {
            WaAttendanceLog::create([
                'wa_user_id' => $user->id,
                'action' => 'check_in',
                'performed_at' => $now,
                'synced' => true,
                'synced_at' => $now,
                'api_response' => $result,
            ]);

            $time = $now->format('H:i');
            $this->sender->sendButtons(
                $user->phone_e164,
                "Check-in registrado a las {$time}. Último estado: IN.",
                [
                    ['id' => 'check_out', 'title' => 'Salir'],
                    ['id' => 'worked_info', 'title' => 'Tiempo trabajado'],
                ]
            );

            Log::info('Check-in OK', ['phone' => $user->phone_e164, 'api_base_url' => $user->api_base_url]);
        } else {
            $this->storeFailedLog($user, 'check_in', $now);
            $this->sender->sendButtons(
                $user->phone_e164,
                'No he podido registrar tu entrada ahora. Inténtalo de nuevo.',
                [['id' => 'check_in', 'title' => 'Reintentar entrada']]
            );
        }
    }

    private function handleCheckOut(WaUser $user): void
    {
        $now = Carbon::now();
        $result = $this->companyApi->checkOut($user);

        if ($result !== null) {
            $workedTime = $this->companyApi->workedTime($user);

            WaAttendanceLog::create([
                'wa_user_id' => $user->id,
                'action' => 'check_out',
                'performed_at' => $now,
                'synced' => true,
                'synced_at' => $now,
                'api_response' => $result,
            ]);

            $time = $now->format('H:i');
            $workedText = $this->formatWorkedTime($workedTime);

            if ($workedTime === null) {
                Log::warning('worked_time unavailable after check-out', [
                    'phone' => $user->phone_e164,
                    'api_base_url' => $user->api_base_url,
                ]);
            }

            $buttons = [
                ['id' => 'worked_info', 'title' => "Trabajado {$workedText}"],
                ['id' => 'check_in', 'title' => 'Entrar'],
            ];

            $this->sender->sendButtons(
                $user->phone_e164,
                "Check-out registrado a las {$time}. Hoy has trabajado: {$workedText}.",
                $buttons
            );

            Log::info('Check-out OK', [
                'phone' => $user->phone_e164,
                'api_base_url' => $user->api_base_url,
                'worked' => $workedText,
            ]);
        } else {
            $this->storeFailedLog($user, 'check_out', $now);
            $this->sender->sendButtons(
                $user->phone_e164,
                'No he podido registrar tu salida ahora. Inténtalo de nuevo.',
                [['id' => 'check_out', 'title' => 'Reintentar salida']]
            );
        }
    }

    private function handleWorkedInfo(WaUser $user): void
    {
        $workedTime = $this->companyApi->workedTime($user);
        $workedText = $this->formatWorkedTime($workedTime);

        if ($workedTime === null) {
            $this->sender->sendButtons(
                $user->phone_e164,
                'No he podido consultar el tiempo trabajado ahora. Inténtalo más tarde.',
                [['id' => 'worked_info', 'title' => 'Reintentar']]
            );

            return;
        }

        $this->sender->sendButtons(
            $user->phone_e164,
            "Hoy has trabajado: {$workedText}.",
            [['id' => 'check_in', 'title' => 'Entrar']]
        );
    }

    private function storeFailedLog(WaUser $user, string $action, Carbon $performedAt): void
    {
        WaAttendanceLog::create([
            'wa_user_id' => $user->id,
            'action' => $action,
            'performed_at' => $performedAt,
            'synced' => false,
            'error_message' => 'Company API unavailable',
        ]);

        Log::warning("Failed {$action}, stored locally for retry", [
            'phone' => $user->phone_e164,
            'api_base_url' => $user->api_base_url,
        ]);
    }

    private function formatWorkedTime(?array $workedTime): string
    {
        if ($workedTime === null) {
            return '0h 0m';
        }

        $hours = $workedTime['hours'] ?? 0;
        $minutes = $workedTime['minutes'] ?? 0;

        return "{$hours}h {$minutes}m";
    }
}
