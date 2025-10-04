<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'access_token',
        'refresh_token',
        'metadata',
        'expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the auth provider.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the avatar URL from metadata.
     */
    public function getAvatarAttribute(): ?string
    {
        return $this->metadata['avatar'] ?? null;
    }

    /**
     * Get the email from metadata.
     */
    public function getEmailAttribute(): ?string
    {
        return $this->metadata['email'] ?? null;
    }

    /**
     * Get the name from metadata.
     */
    public function getNameAttribute(): ?string
    {
        return $this->metadata['name'] ?? null;
    }

    /**
     * Get the username from metadata.
     */
    public function getUsernameAttribute(): ?string
    {
        return $this->metadata['username'] ?? $this->metadata['nickname'] ?? null;
    }

    /**
     * Check if the access token is expired.
     */
    public function isTokenExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
