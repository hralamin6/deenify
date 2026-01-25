<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'donation_id',
        'gateway',
        'status',
        'amount',
        'currency',
        'provider_reference',
        'redirect_url',
        'response_payload',
        'initiated_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'response_payload' => 'array',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }
}
