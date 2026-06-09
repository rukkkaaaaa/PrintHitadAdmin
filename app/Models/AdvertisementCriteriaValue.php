<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementCriteriaValue extends Model
{
    use HasFactory;
    protected $fillable = ['advertisement_id', 'advertisement_criteria_id', 'advertisement_criteria_option_value'];


    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }


    public function criteria()
    {
        return $this->belongsTo(AdvertisementCriteria::class);
    }
}
