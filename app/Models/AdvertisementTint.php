<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementTint extends Model
{
    use HasFactory;
    protected $fillable = ['advertisement_tint_en', 'advertisement_tint_si', 'is_active', 'price'];
    
}
