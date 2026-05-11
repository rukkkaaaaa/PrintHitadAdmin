<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $fillable = [
        'payment_method_name',
        'bank',
        'duration',
        'handling_fee',
        'merchant',
        'client_id',
        'client_secret',
        'base_url',
        'A/C_NO',
        'A/C_Holder',
        'is_active'
    ];
}
