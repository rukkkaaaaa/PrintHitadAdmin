<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementSize extends Model
{
    use HasFactory;
    protected $fillable = ['advertisement_type_id', 'advertisement_size_en', 'advertisement_size_si', 'ad_word_count', 'description', 'max_images', 'is_active', 'price', 'img_url'];


    public function advertisementType()
    {
        return $this->belongsTo(AdvertisementType::class);
    }
}
