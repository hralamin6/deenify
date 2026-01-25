<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'donation_id',
        'receipt_number',
        'issued_at',
        'pdf_path',
        'meta',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'meta' => 'array',
    ];

    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }
}
