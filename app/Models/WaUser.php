<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaUser extends Model
{
    protected $fillable = [
        'phone_e164',
        'name',
        'api_base_url',
        'api_token',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(WaAttendanceLog::class);
    }
}
