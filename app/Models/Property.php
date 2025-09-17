<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Seoable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, Seoable, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'listing_type',
        'status',
        'price',
        'monthly_rent',
        'currency',
        'bedrooms',
        'bathrooms',
        'total_rooms',
        'area_sqft',
        'land_area_sqft',
        'floors',
        'floor_number',
        'built_year',
        'address',
        'latitude',
        'longitude',
        'postal_code',
        'is_furnished',
        'has_parking',
        'parking_spaces',
        'pet_friendly',
        'is_featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'property_type_id',
        'location_id',
        'agent_id',
        'views_count',
        'favorites_count',
        'inquiries_count',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'monthly_rent' => 'decimal:2',
            'area_sqft' => 'decimal:2',
            'land_area_sqft' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_furnished' => 'boolean',
            'has_parking' => 'boolean',
            'pet_friendly' => 'boolean',
            'is_featured' => 'boolean',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'total_rooms' => 'integer',
            'floors' => 'integer',
            'floor_number' => 'integer',
            'parking_spaces' => 'integer',
            'views_count' => 'integer',
            'favorites_count' => 'integer',
            'inquiries_count' => 'integer',
        ];
    }

    /**
     * Get the property type.
     */
    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    /**
     * Get the location.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the agent.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the property images.
     */
    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class);
    }

    /**
     * Get the primary image.
     */
    public function primaryImage(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->where('is_primary', true);
    }

    /**
     * Get the amenities.
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'property_amenities');
    }

    /**
     * Get the inquiries.
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    /**
     * Get the favorites.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Scope for available properties.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope for featured properties.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for sale properties.
     */
    public function scopeForSale($query)
    {
        return $query->where('listing_type', 'sale');
    }

    /**
     * Scope for rent properties.
     */
    public function scopeForRent($query)
    {
        return $query->where('listing_type', 'rent');
    }

    /**
     * Scope for price range.
     */
    public function scopePriceRange($query, $minPrice, $maxPrice)
    {
        return $query->where('price', '>=', $minPrice)
            ->where('price', '<=', $maxPrice);
    }

    /**
     * Scope for bedrooms.
     */
    public function scopeWithBedrooms($query, int $bedrooms)
    {
        return $query->where('bedrooms', '>=', $bedrooms);
    }

    /**
     * Scope for bathrooms.
     */
    public function scopeWithBathrooms($query, int $bathrooms)
    {
        return $query->where('bathrooms', '>=', $bathrooms);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price, 0) . ' ' . $this->currency;
    }

    /**
     * Get property URL.
     */
    public function getUrlAttribute(): string
    {
        return route('properties.show', $this->slug);
    }
}
