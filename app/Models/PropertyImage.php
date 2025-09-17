<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'image_path',
        'image_url',
        'title',
        'alt_text',
        'type',
        'is_primary',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the property.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the full image URL.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->attributes['image_url']) {
            return $this->attributes['image_url'];
        }

        return asset('storage/' . $this->image_path);
    }

    /**
     * Scope for primary images.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope for gallery images.
     */
    public function scopeGallery($query)
    {
        return $query->where('type', 'gallery');
    }

    /**
     * Scope for floor plans.
     */
    public function scopeFloorPlan($query)
    {
        return $query->where('type', 'floor_plan');
    }

    /**
     * Order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
