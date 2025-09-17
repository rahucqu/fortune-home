<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'license_number',
        'bio',
        'avatar',
        'office_address',
        'social_media',
        'is_active',
        'commission_rate',
        'properties_sold',
        'experience_years',
    ];

    protected function casts(): array
    {
        return [
            'social_media' => 'array',
            'is_active' => 'boolean',
            'commission_rate' => 'decimal:2',
            'properties_sold' => 'integer',
            'experience_years' => 'integer',
        ];
    }

    /**
     * Get the properties for this agent.
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Get the inquiries assigned to this agent.
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'responded_by');
    }

    /**
     * Get active agents.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Order by name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Get agent's full contact information.
     */
    public function getFullContactAttribute(): string
    {
        $contact = $this->email;
        if ($this->phone) {
            $contact .= ' | ' . $this->phone;
        }

        return $contact;
    }
}
