<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;
    protected $fillable = [       
        'user_id',
        'payment',
        'payment_token',
        'payment_type',
        'payment_status',
        'currency',
        'gateway_reference',
        'failure_reason',
        'paid_at',
    ];

    protected $table = 'settlement';

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function isPaystackPayout(): bool
    {
        return strtoupper((string) $this->payment_type) === 'PAYSTACK';
    }
}
