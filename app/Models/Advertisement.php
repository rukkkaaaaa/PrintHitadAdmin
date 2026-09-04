<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'category_id',
        'district_id',
        'city_id',
        'promo_code_id',
        'discount_amount',
        'order_ref',
        'price',
        'ad_title',
        'advertisement_description',
        'retyped_advertisement_description',
        'reference_number',
        'publish_date',
        'publication',
        'web_combined_ad_hitadlk',
        'print_combined_ad_hitadprint',
    ];

    protected $casts = [
        'publish_date' => 'date',
        'web_combined_ad_hitadlk' => 'boolean',
        'print_combined_ad_hitadprint' => 'boolean',
        'price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function criteriaValues(): HasMany
    {
        return $this->hasMany(AdvertisementCriteriaValue::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(AdvertisementImage::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'advertisement_id');
    }

    /**
     * Get the newest payment belonging to this advertisement.
     */
    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class, 'advertisement_id')
            ->latestOfMany();
    }
}