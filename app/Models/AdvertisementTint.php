<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementTint extends Model
{
    use HasFactory;

    protected $fillable = ['advertisement_tint_en', 'advertisement_tint_si', 'color', 'is_active', 'price'];

    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'category_has_advertisement_tints',
            'advertisement_tint_id',
            'category_id'
        );
    }
}
