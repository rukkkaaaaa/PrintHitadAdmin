<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertisement_id',
        'payment_method_id',
        'price_breakdown',
        'amount',
        'payment_status',
        'session_id',
        'success_indicator',
        'result',
        'payment_date',
        'is_success',
        'receipt_number',
        'payment_slip_file_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'is_success' => 'boolean',
        'price_breakdown' => 'array',
    ];

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class, 'advertisement_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}