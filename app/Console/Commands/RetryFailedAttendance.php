<?php

namespace App\Console\Commands;

use App\Models\WaAttendanceLog;
use App\Services\CompanyApiClient;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RetryFailedAttendance extends Command
{
    protected $signature = 'attendance:retry {--limit=50 : Max records to process}';

    protected $description = 'Retry syncing failed attendance logs to company APIs';

    public function handle(CompanyApiClient $companyApi): int
    {
        $limit = (int) $this->option('limit');

        $pendingLogs = WaAttendanceLog::with('waUser')
            ->where('synced', false)
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($pendingLogs->isEmpty()) {
            $this->info('No pending attendance logs to retry.');

            return self::SUCCESS;
        }

        $this->info("Retrying {$pendingLogs->count()} pending attendance logs...");

        $success = 0;
        $failed = 0;

        foreach ($pendingLogs as $log) {
            $user = $log->waUser;

            if (! $user || ! $user->active) {
                $log->update(['error_message' => 'User inactive or deleted']);
                $failed++;

                continue;
            }

            $result = match ($log->action) {
                'check_in' => $companyApi->checkIn($user),
                'check_out' => $companyApi->checkOut($user),
                default => null,
            };

            if ($result !== null) {
                $log->update([
                    'synced' => true,
                    'synced_at' => Carbon::now(),
                    'api_response' => $result,
                    'error_message' => null,
                ]);
                $success++;
                $this->line("  OK: {$user->phone_e164} — {$log->action}");
            } else {
                $log->update(['error_message' => 'Retry failed — Company API unavailable']);
                $failed++;
                $this->warn("  FAIL: {$user->phone_e164} — {$log->action}");
            }
        }

        $this->info("Done. Success: {$success}, Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
