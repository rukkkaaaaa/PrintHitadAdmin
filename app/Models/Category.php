<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['category_name_en', 'category_name_si', 'is_active'];


    public function advertisements()
    {
        return $this->hasMany(Advertisement::class);
    }


    public function criterias()
    {
        return $this->hasMany(AdvertisementCriteria::class);
    }

    public function advertisementTypes()
    {
        return $this->hasMany(AdvertisementType::class);
    }
}
