<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementCriteriaOption extends Model
{
    use HasFactory;
    protected $fillable = ['advertisement_criteria_id', 'advertisement_criteria_option_name_en', 'advertisement_criteria_option_name_si', 'is_active'];


    public function criteria()
    {
        return $this->belongsTo(AdvertisementCriteria::class);
    }
}
