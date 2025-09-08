<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SeoSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('key');
    }

    // Accessors
    public function getFormattedValueAttribute(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    // Static methods for easy access
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "seo_setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::active()->where('key', $key)->first();
            
            return $setting ? $setting->formatted_value : $default;
        });
    }

    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): self
    {
        $formattedValue = match ($type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $formattedValue,
                'type' => $type,
                'group' => $group,
                'is_active' => true,
            ]
        );

        // Clear cache
        Cache::forget("seo_setting_{$key}");

        return $setting;
    }

    public static function getByGroup(string $group): Collection
    {
        $cacheKey = "seo_settings_group_{$group}";
        
        return Cache::remember($cacheKey, 3600, function () use ($group) {
            return static::active()
                ->byGroup($group)
                ->ordered()
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => $setting->formatted_value];
                });
        });
    }

    public static function getAllSettings(): Collection
    {
        return Cache::remember('all_seo_settings', 3600, function () {
            return static::active()
                ->ordered()
                ->get()
                ->groupBy('group')
                ->map(function ($groupSettings) {
                    return $groupSettings->mapWithKeys(function ($setting) {
                        return [$setting->key => $setting->formatted_value];
                    });
                });
        });
    }

    public static function clearCache(): void
    {
        Cache::flush();
    }

    // Boot method
    protected static function boot(): void
    {
        parent::boot();

        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    // Default SEO settings
    public static function getDefaults(): array
    {
        return [
            // General SEO
            'site_title' => [
                'value' => config('app.name', 'Laravel Blog'),
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default site title',
            ],
            'site_description' => [
                'value' => 'A powerful blog built with Laravel',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Default site description',
            ],
            'site_keywords' => [
                'value' => 'laravel, blog, cms, php',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default site keywords',
            ],
            'title_separator' => [
                'value' => ' | ',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Separator between page title and site title',
            ],
            'default_og_image' => [
                'value' => '/images/default-og-image.jpg',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Default Open Graph image URL',
            ],
            
            // Social Media
            'twitter_handle' => [
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Twitter handle (without @)',
            ],
            'facebook_app_id' => [
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Facebook App ID',
            ],
            
            // Analytics
            'google_analytics_id' => [
                'value' => '',
                'type' => 'string',
                'group' => 'analytics',
                'description' => 'Google Analytics tracking ID',
            ],
            'google_tag_manager_id' => [
                'value' => '',
                'type' => 'string',
                'group' => 'analytics',
                'description' => 'Google Tag Manager ID',
            ],
        ];
    }
}
