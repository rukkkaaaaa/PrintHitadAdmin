<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementCriteria extends Model
{
    use HasFactory;
    protected $fillable = ['category_id', 'advertisement_criteria_name_en', 'advertisement_criteria_name_si', 'field_type', 'is_active'];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function options()
    {
        return $this->hasMany(AdvertisementCriteriaOption::class);
    }
}
