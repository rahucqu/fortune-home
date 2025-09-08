<?php

declare(strict_types=1);

namespace App\Traits\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToUser
{
    public static function bootBelongsToUser(): void
    {
        static::creating(static function (Model $model) {
            $model->attributes['user_id'] = $model->user_id ?? auth()->id();
        });
    }

    public function scopeForUser(Builder $query, string|int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUser(Builder $query): Builder
    {
        return $query->forUser(auth()->id());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
