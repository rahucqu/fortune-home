<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'name',
        'email',
        'phone',
        'message',
        'inquiry_type',
        'status',
        'preferred_contact_time',
        'preferred_contact_method',
        'agent_notes',
        'responded_at',
        'responded_by',
    ];

    protected function casts(): array
    {
        return [
            'preferred_contact_time' => 'datetime',
            'responded_at' => 'datetime',
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
     * Get the user who made the inquiry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who responded to the inquiry.
     */
    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * Scope for pending inquiries.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for responded inquiries.
     */
    public function scopeResponded($query)
    {
        return $query->where('status', 'responded');
    }

    /**
     * Scope for inquiry type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('inquiry_type', $type);
    }

    /**
     * Mark inquiry as responded.
     */
    public function markAsResponded(int $userId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'responded',
            'responded_at' => now(),
            'responded_by' => $userId,
            'agent_notes' => $notes,
        ]);
    }
}
