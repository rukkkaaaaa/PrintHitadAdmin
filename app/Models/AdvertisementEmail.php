<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertisement_id',
        'customer_email',
        'customer_name',
        'amount',
        'status',
        'error_message',
    ];

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }
}
