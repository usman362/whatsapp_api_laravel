<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaAttendanceLog extends Model
{
    protected $fillable = [
        'wa_user_id',
        'action',
        'performed_at',
        'synced',
        'synced_at',
        'api_response',
        'error_message',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'synced' => 'boolean',
        'synced_at' => 'datetime',
        'api_response' => 'array',
    ];

    public function waUser(): BelongsTo
    {
        return $this->belongsTo(WaUser::class);
    }
}
