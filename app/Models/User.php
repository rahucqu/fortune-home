<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasProfilePhoto;
use App\Traits\HasReviewable;
use App\Traits\HasSocialAuth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasProfilePhoto, HasReviewable, HasRoles, HasSocialAuth, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'bio',
        'license_number',
        'company',
        'designation',
        'address',
        'city',
        'state',
        'zip_code',
        'social_links',
        'is_active',
        'email_notifications',
        'sms_notifications',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'social_links' => 'array',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['profile_photo_url', 'has_password'];

    /**
     * Get the properties listed by this user (if agent).
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'agent_id');
    }

    /**
     * Get the property inquiries made by this user.
     */
    public function propertyInquiries(): HasMany
    {
        return $this->hasMany(PropertyInquiry::class);
    }

    /**
     * Get the contact inquiries assigned to this user.
     */
    public function assignedContactInquiries(): HasMany
    {
        return $this->hasMany(ContactInquiry::class, 'assigned_to');
    }

    /**
     * Get the user's favorite properties.
     */
    public function favoriteProperties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_favorites')
            ->withTimestamps();
    }

    /**
     * Get array of favorite property IDs.
     */
    public function getFavoritePropertyIds(): array
    {
        return $this->favoriteProperties()->pluck('properties.id')->toArray();
    }

    /**
     * Check if user has favorited a property.
     */
    public function hasFavorited(Property $property): bool
    {
        return $this->favoriteProperties()->where('properties.id', $property->id)->exists();
    }

    /**
     * Get the user's saved searches.
     */
    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class);
    }

    /**
     * Get the property views by this user.
     */
    public function propertyViews(): HasMany
    {
        return $this->hasMany(PropertyView::class);
    }

    /**
     * Get the blog posts authored by this user.
     */
    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    /**
     * Get the blog comments made by this user.
     */
    public function blogComments(): HasMany
    {
        return $this->hasMany(BlogComment::class);
    }

    /**
     * Get the blog edit requests made by this user.
     */
    public function blogEditRequests(): HasMany
    {
        return $this->hasMany(BlogEditRequest::class, 'requested_by');
    }

    /**
     * Get the blog edit requests reviewed by this user.
     */
    public function reviewedBlogEditRequests(): HasMany
    {
        return $this->hasMany(BlogEditRequest::class, 'reviewed_by');
    }

    /**
     * Get the social accounts for this user.
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get messages sent by this user.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'from_user_id');
    }

    /**
     * Get messages received by this user.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'to_user_id');
    }

    /**
     * Get all messages for this user (sent and received).
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'from_user_id')
            ->union($this->hasMany(Message::class, 'to_user_id'));
    }

    /**
     * Get unread messages count for this user.
     */
    public function getUnreadMessagesCountAttribute(): int
    {
        return $this->receivedMessages()->unread()->count();
    }

    /**
     * Check if user is an agent.
     */
    public function isAgent(): bool
    {
        return $this->hasRole('agent');
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('super-admin');
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Check if user is a customer.
     */
    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    /**
     * Get the user's full address.
     */
    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->zip_code,
        ]);

        return ! empty($parts) ? implode(', ', $parts) : null;
    }

    /**
     * Get the user's initials.
     */
    public function getInitialsAttribute(): string
    {
        $names = explode(' ', $this->name);
        $initials = '';

        foreach ($names as $name) {
            if (! empty($name)) {
                $initials .= strtoupper($name[0]);
            }
        }

        return $initials ?: 'U';
    }

    /**
     * Check if user has completed their profile.
     */
    public function hasCompleteProfile(): bool
    {
        $requiredFields = ['name', 'email', 'phone'];

        if ($this->isAgent()) {
            $requiredFields = array_merge($requiredFields, ['license_number', 'company']);
        }

        foreach ($requiredFields as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Scope for active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for agents.
     */
    public function scopeAgents($query)
    {
        return $query->role('agent');
    }

    /**
     * Scope for customers.
     */
    public function scopeCustomers($query)
    {
        return $query->role('customer');
    }

    /**
     * Scope for admins.
     */
    public function scopeAdmins($query)
    {
        return $query->role(['admin', 'super-admin']);
    }

    /**
     * Get users with verified emails.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Get users in a specific city.
     */
    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Get users in a specific state.
     */
    public function scopeInState($query, string $state)
    {
        return $query->where('state', $state);
    }
}
