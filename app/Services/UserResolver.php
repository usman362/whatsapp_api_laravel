<?php

namespace App\Services;

use App\Models\WaUser;

class UserResolver
{
    public function resolve(string $phone): ?WaUser
    {
        $normalized = $this->normalizePhone($phone);

        return WaUser::where('phone_e164', $normalized)
            ->where('active', true)
            ->first();
    }

    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        if (! str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }
}
