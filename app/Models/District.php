<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;
    protected $fillable = ['district_name_en', 'district_name_si', 'is_active'];


    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
