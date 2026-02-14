<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneOtp extends Model
{
    protected $fillable = [
        'phone',
        'otp',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
