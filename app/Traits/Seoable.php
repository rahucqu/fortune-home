<?php

declare(strict_types=1);

namespace App\Traits;

use App\Services\SeoService;
use Exception;
use Illuminate\Support\Str;

trait Seoable
{
    /**
     * Get the SEO title for this model
     */
    public function getSeoTitle(): string
    {
        // Check for model-specific SEO title fields
        if (isset($this->meta_title) && ! empty($this->meta_title)) {
            return $this->meta_title;
        }

        if (isset($this->seo_title) && ! empty($this->seo_title)) {
            return $this->seo_title;
        }

        // Fallback to title or name
        return $this->title ?? $this->name ?? '';
    }

    /**
     * Get the SEO description for this model
     */
    public function getSeoDescription(): string
    {
        // Check for model-specific SEO description fields
        if (isset($this->meta_description) && ! empty($this->meta_description)) {
            return $this->meta_description;
        }

        if (isset($this->seo_description) && ! empty($this->seo_description)) {
            return $this->seo_description;
        }

        // Fallback to excerpt, description, or content
        if (isset($this->excerpt) && ! empty($this->excerpt)) {
            return $this->excerpt;
        }

        if (isset($this->description) && ! empty($this->description)) {
            return $this->description;
        }

        if (isset($this->content)) {
            return Str::limit(strip_tags($this->content), 160);
        }

        return '';
    }

    /**
     * Get the SEO keywords for this model
     */
    public function getSeoKeywords(): string
    {
        // Check for model-specific SEO keywords fields
        if (isset($this->meta_keywords) && ! empty($this->meta_keywords)) {
            return $this->meta_keywords;
        }

        if (isset($this->seo_keywords) && ! empty($this->seo_keywords)) {
            return $this->seo_keywords;
        }

        // Auto-generate keywords for posts
        if (method_exists($this, 'tags') && $this->relationLoaded('tags')) {
            $keywords = $this->tags->pluck('name')->toArray();

            if (method_exists($this, 'category') && $this->relationLoaded('category') && $this->category) {
                array_unshift($keywords, $this->category->name);
            }

            return implode(', ', array_unique($keywords));
        }

        return '';
    }

    /**
     * Get the Open Graph image for this model
     */
    public function getOgImage(): string
    {
        // Check for featured image
        if (method_exists($this, 'featuredImage') && $this->relationLoaded('featuredImage') && $this->featuredImage) {
            return $this->featuredImage->url;
        }

        // Check for image field
        if (isset($this->image) && ! empty($this->image)) {
            return $this->image;
        }

        // Fallback to default
        return config('app.url') . '/images/default-og-image.jpg';
    }

    /**
     * Get the canonical URL for this model
     */
    public function getCanonicalUrl(): string
    {
        $routeKey = $this->getRouteKey();

        // Try to generate route based on model name
        $modelName = strtolower(class_basename($this));

        try {
            return route("{$modelName}s.show", $routeKey);
        } catch (Exception $e) {
            // Fallback to generic URL
            return config('app.url') . "/{$modelName}s/{$routeKey}";
        }
    }

    /**
     * Generate complete SEO meta data for this model
     */
    public function generateSeoMeta(): array
    {
        $seoService = app(SeoService::class);

        $modelName = strtolower(class_basename($this));

        // Call appropriate method based on model type
        return match ($modelName) {
            'post' => $seoService->generatePostMeta($this),
            'category' => $seoService->generateCategoryMeta($this),
            'tag' => $seoService->generateTagMeta($this),
            default => $this->getBasicSeoMeta(),
        };
    }

    /**
     * Get basic SEO meta data for unknown models
     */
    protected function getBasicSeoMeta(): array
    {
        $title = $this->getSeoTitle();
        $description = $this->getSeoDescription();
        $keywords = $this->getSeoKeywords();
        $ogImage = $this->getOgImage();
        $canonicalUrl = $this->getCanonicalUrl();

        return [
            'title' => $title,
            'meta_title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $ogImage,
            'og_url' => $canonicalUrl,
            'og_type' => 'website',
            'twitter_card' => 'summary',
            'twitter_title' => $title,
            'twitter_description' => $description,
            'twitter_image' => $ogImage,
        ];
    }

    /**
     * Auto-populate SEO fields before saving
     */
    public function autoPopulateSeoFields(): void
    {
        // Auto-populate meta_title if empty
        if (isset($this->meta_title) && empty($this->meta_title) && ! empty($this->title)) {
            $this->meta_title = $this->title;
        }

        // Auto-populate seo_title if empty
        if (isset($this->seo_title) && empty($this->seo_title) && ! empty($this->name)) {
            $this->seo_title = $this->name;
        }

        // Auto-populate description fields
        if (isset($this->meta_description) && empty($this->meta_description)) {
            if (isset($this->excerpt) && ! empty($this->excerpt)) {
                $this->meta_description = $this->excerpt;
            } elseif (isset($this->content)) {
                $this->meta_description = Str::limit(strip_tags($this->content), 160);
            }
        }

        if (isset($this->seo_description) && empty($this->seo_description) && isset($this->description)) {
            $this->seo_description = $this->description;
        }
    }

    /**
     * Boot the trait
     */
    public static function bootSeoable(): void
    {
        static::saving(function ($model) {
            $model->autoPopulateSeoFields();
        });
    }
}
