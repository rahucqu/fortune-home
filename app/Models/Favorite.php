<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'notes',
    ];

    /**
     * Get the user who favorited the property.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the favorited property.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Check if a property is favorited by a user.
     */
    public static function isFavorited(int $userId, int $propertyId): bool
    {
        return static::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->exists();
    }

    /**
     * Toggle favorite status.
     */
    public static function toggle(int $userId, int $propertyId): bool
    {
        $favorite = static::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return false;
        } else {
            static::create([
                'user_id' => $userId,
                'property_id' => $propertyId,
            ]);

            return true;
        }
    }
}
