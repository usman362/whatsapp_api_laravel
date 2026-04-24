<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class WaSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'encrypted',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $setting->value ?? $default;
    }

    public static function setValue(string $key, mixed $value, bool $encrypted = false): self
    {
        /** @var self $setting */
        $setting = static::query()->firstOrNew(['key' => $key]);
        $setting->encrypted = $encrypted;
        $setting->value = is_null($value) ? null : (string) $value;
        $setting->save();

        return $setting;
    }

    public function getValueAttribute($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! $this->encrypted) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setValueAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['value'] = null;

            return;
        }

        $value = (string) $value;

        $this->attributes['value'] = $this->encrypted
            ? Crypt::encryptString($value)
            : $value;
    }
}

