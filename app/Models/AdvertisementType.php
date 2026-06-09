<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementType extends Model
{
    use HasFactory;
    protected $fillable = ['category_id', 'advertisement_type_en', 'advertisement_type_si', 'is_active', 'price'];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function advertisementSizes()
    {
        return $this->hasMany(AdvertisementSize::class);
    }
}
