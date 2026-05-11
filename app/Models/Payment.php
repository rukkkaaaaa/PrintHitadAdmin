<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'advertisement_id',
        'payment_method_id',
        'amount',
        'payment_status',
        'session_id',
        'success_indicator',
        'result',
        'payment_date',
        'is_success'
    ];


    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }


    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
