<?php

namespace App\Services;

use App\Models\WaSetting;
use Illuminate\Support\Facades\Cache;

class AppSettings
{
    private const CACHE_KEY_PREFIX = 'wa_settings:';
    private const CACHE_TTL_SECONDS = 300;

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(
            self::CACHE_KEY_PREFIX . $key,
            self::CACHE_TTL_SECONDS,
            fn () => WaSetting::getValue($key, $default)
        );
    }

    public function set(string $key, mixed $value, bool $encrypted = false): void
    {
        WaSetting::setValue($key, $value, $encrypted);
        Cache::forget(self::CACHE_KEY_PREFIX . $key);
    }
}

