<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    protected $fillable = ['city_name', 'city_name', 'district_id', 'is_active'];


    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
