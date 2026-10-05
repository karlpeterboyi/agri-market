<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
    'provider_id',
    'service_category_id',
    'title',
    'description',
    'price',
    'pricing_type',
    'phone',
    'mobile',           
    'email',
    'website',
    'region',
    'district',
    'ward',
    'village',
    'latitude',
    'longitude',
    'cover_photo',
    'gallery',
    'features',         
    'available_from',
    'available_to',
    'verified',
    'featured',
    'status',
];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $appends = [
        'business_name',
        'starting_price',
        'cover_image',
    ];

    protected $casts = [
        'gallery' => 'array',
        'mobile' => 'string',
        'verified' => 'boolean',
        'featured' => 'boolean',
        'features' => 'array',
        'price' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the provider (user) who offers this service.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Get the category that this service belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

/**
 * Get the provider that owns the service.
 */
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function quotes()
{
    return $this->hasMany(ServiceQuote::class);
}

public function providerProfile()
{
    return $this->belongsTo(
        ProviderProfile::class,
        'provider_id',
        'user_id'
    );
}

public function getBusinessNameAttribute(): string
    {
        return (string) ($this->attributes['title'] ?? '');
    }

    public function getStartingPriceAttribute()
    {
        return $this->attributes['price'] ?? 0;
    }

    public function getCoverImageAttribute()
    {
        return $this->attributes['cover_photo'] ?? null;
    }

    public function packages()
{
    return $this->hasMany(ServicePackage::class);
}

}
