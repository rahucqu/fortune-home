<?php

declare(strict_types=1);

namespace App\Traits\Eloquent;

use App\Contracts\TeamDiscoveryService;
use App\Models\Scopes\TeamScope;
use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTeam
{
    public static $teamRelationKey = 'team_id';

    public static function bootBelongsToTeam(): void
    {
        static::addGlobalScope(TeamScope::class, new TeamScope());

        static::creating(static function (Model $model) {
            $model->setAttribute(self::$teamRelationKey, $model->{self::$teamRelationKey} ?: app(TeamDiscoveryService::class)->teamId());

            $model->setRelation('team', app(TeamDiscoveryService::class)->currentTeam());
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, self::$teamRelationKey);
    }

    public function scopeWithTeam($query): void
    {
        $query->with(['team']);
    }
}
