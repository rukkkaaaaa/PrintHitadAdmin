<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'category_id',
        'district_id',
        'city_id',
        'order_ref',
        'price',
        'ad_title',
        'advertisement_type_id',
        'advertisement_size_id',
        'advertisement_description',
        'publish_date',
        'publication',
        'web_combined_ad',
        'status'
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function district()
    {
        return $this->belongsTo(District::class);
    }


    public function city()
    {
        return $this->belongsTo(City::class);
    }


    public function criteriaValues()
    {
        return $this->hasMany(AdvertisementCriteriaValue::class);
    }


    public function images()
    {
        return $this->hasMany(AdvertisementImage::class);
    }


    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
