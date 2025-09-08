<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\SeoSetting;
use App\Models\Tag;
use Exception;
use Illuminate\Support\Str;

class SeoService
{
    public function __construct()
    {
        //
    }

    /**
     * Generate SEO meta data for a post
     */
    public function generatePostMeta(Post $post): array
    {
        $siteTitle = SeoSetting::get('site_title', config('app.name'));
        $titleSeparator = SeoSetting::get('title_separator', ' | ');
        $defaultDescription = SeoSetting::get('site_description', '');
        $defaultKeywords = SeoSetting::get('site_keywords', '');
        $defaultOgImage = SeoSetting::get('default_og_image', '');

        // Generate title
        $title = $post->meta_title ?: $post->title;
        $fullTitle = $title . $titleSeparator . $siteTitle;

        // Generate description
        $description = $post->meta_description ?: $post->excerpt ?: Str::limit(strip_tags($post->content), 160);
        if (empty($description)) {
            $description = $defaultDescription;
        }

        // Generate keywords
        $keywords = $post->meta_keywords;
        if (empty($keywords)) {
            $tagNames = $post->tags->pluck('name')->toArray();
            $categoryName = $post->category?->name;
            $autoKeywords = array_filter([
                $categoryName,
                ...$tagNames,
                ...explode(', ', $defaultKeywords),
            ]);
            $keywords = implode(', ', array_unique($autoKeywords));
        }

        // Generate Open Graph image
        $ogImage = $post->featuredImage?->url ?? $defaultOgImage;

        // Generate URLs safely
        try {
            $ogUrl = route('posts.show', $post->slug ?? $post->id);
        } catch (Exception $e) {
            $ogUrl = config('app.url') . '/posts/' . ($post->slug ?? $post->id);
        }

        return [
            'title' => $fullTitle,
            'meta_title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $ogImage,
            'og_url' => $ogUrl,
            'og_type' => 'article',
            'article_author' => $post->user?->name,
            'article_published_time' => $post->published_at?->toISOString(),
            'article_modified_time' => $post->updated_at->toISOString(),
            'article_section' => $post->category?->name,
            'article_tag' => $post->tags->pluck('name')->toArray(),
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $title,
            'twitter_description' => $description,
            'twitter_image' => $ogImage,
            'twitter_site' => '@' . SeoSetting::get('twitter_handle', ''),
        ];
    }

    /**
     * Generate SEO meta data for a category
     */
    public function generateCategoryMeta(Category $category): array
    {
        $siteTitle = SeoSetting::get('site_title', config('app.name'));
        $titleSeparator = SeoSetting::get('title_separator', ' | ');
        $defaultDescription = SeoSetting::get('site_description', '');
        $defaultKeywords = SeoSetting::get('site_keywords', '');
        $defaultOgImage = SeoSetting::get('default_og_image', '');

        // Generate title
        $title = $category->seo_title ?: $category->name;
        $fullTitle = $title . $titleSeparator . $siteTitle;

        // Generate description
        $description = $category->seo_description ?: $category->description ?: "Posts in {$category->name} category";
        if (empty($description)) {
            $description = $defaultDescription;
        }

        // Generate keywords
        $keywords = $category->seo_keywords ?: "{$category->name}, {$defaultKeywords}";

        // Generate Open Graph image
        $ogImage = $category->image ?? $defaultOgImage;

        // Generate URLs safely
        try {
            $ogUrl = route('categories.show', $category->slug ?? $category->id);
        } catch (Exception $e) {
            $ogUrl = config('app.url') . '/categories/' . ($category->slug ?? $category->id);
        }

        return [
            'title' => $fullTitle,
            'meta_title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $ogImage,
            'og_url' => $ogUrl,
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $title,
            'twitter_description' => $description,
            'twitter_image' => $ogImage,
            'twitter_site' => '@' . SeoSetting::get('twitter_handle', ''),
        ];
    }

    /**
     * Generate SEO meta data for a tag
     */
    public function generateTagMeta(Tag $tag): array
    {
        $siteTitle = SeoSetting::get('site_title', config('app.name'));
        $titleSeparator = SeoSetting::get('title_separator', ' | ');
        $defaultDescription = SeoSetting::get('site_description', '');
        $defaultKeywords = SeoSetting::get('site_keywords', '');
        $defaultOgImage = SeoSetting::get('default_og_image', '');

        // Generate title
        $title = $tag->seo_title ?: $tag->name;
        $fullTitle = $title . $titleSeparator . $siteTitle;

        // Generate description
        $description = $tag->seo_description ?: $tag->description ?: "Posts tagged with {$tag->name}";
        if (empty($description)) {
            $description = $defaultDescription;
        }

        // Generate keywords
        $keywords = $tag->seo_keywords ?: "{$tag->name}, {$defaultKeywords}";

        // Generate URLs safely
        try {
            $ogUrl = route('tags.show', $tag->slug ?? $tag->id);
        } catch (Exception $e) {
            $ogUrl = config('app.url') . '/tags/' . ($tag->slug ?? $tag->id);
        }

        return [
            'title' => $fullTitle,
            'meta_title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $defaultOgImage,
            'og_url' => $ogUrl,
            'og_type' => 'website',
            'twitter_card' => 'summary',
            'twitter_title' => $title,
            'twitter_description' => $description,
            'twitter_image' => $defaultOgImage,
            'twitter_site' => '@' . SeoSetting::get('twitter_handle', ''),
        ];
    }

    /**
     * Generate SEO meta data for homepage
     */
    public function generateHomepageMeta(): array
    {
        $siteTitle = SeoSetting::get('site_title', config('app.name'));
        $description = SeoSetting::get('site_description', '');
        $keywords = SeoSetting::get('site_keywords', '');
        $ogImage = SeoSetting::get('default_og_image', '');

        // Generate URLs safely
        try {
            $homeUrl = route('home');
        } catch (Exception $e) {
            $homeUrl = config('app.url');
        }

        return [
            'title' => $siteTitle,
            'meta_title' => $siteTitle,
            'description' => $description,
            'keywords' => $keywords,
            'og_title' => $siteTitle,
            'og_description' => $description,
            'og_image' => $ogImage,
            'og_url' => $homeUrl,
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $siteTitle,
            'twitter_description' => $description,
            'twitter_image' => $ogImage,
            'twitter_site' => '@' . SeoSetting::get('twitter_handle', ''),
        ];
    }

    /**
     * Generate JSON-LD structured data for a post
     */
    public function generatePostJsonLd(Post $post): array
    {
        $siteTitle = SeoSetting::get('site_title', config('app.name'));
        $ogImage = $post->featuredImage?->url ?? SeoSetting::get('default_og_image', '');

        // Generate URLs safely
        try {
            $postUrl = route('posts.show', $post->slug ?? $post->id);
        } catch (Exception $e) {
            $postUrl = config('app.url') . '/posts/' . ($post->slug ?? $post->id);
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $post->excerpt ?: Str::limit(strip_tags($post->content), 160),
            'image' => $ogImage,
            'author' => [
                '@type' => 'Person',
                'name' => $post->user?->name,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteTitle,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => SeoSetting::get('default_og_image', ''),
                ],
            ],
            'datePublished' => $post->published_at?->toISOString(),
            'dateModified' => $post->updated_at->toISOString(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $postUrl,
            ],
            'articleSection' => $post->category?->name,
            'keywords' => $post->tags->pluck('name')->toArray(),
        ];
    }

    /**
     * Generate breadcrumb JSON-LD
     */
    public function generateBreadcrumbJsonLd(array $breadcrumbs): array
    {
        $itemListElement = [];

        foreach ($breadcrumbs as $index => $breadcrumb) {
            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $breadcrumb['name'],
                'item' => $breadcrumb['url'] ?? null,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElement,
        ];
    }

    /**
     * Generate meta tags HTML
     */
    public function generateMetaTagsHtml(array $metaData): string
    {
        $html = [];

        // Basic meta tags
        if (! empty($metaData['description'])) {
            $html[] = '<meta name="description" content="' . htmlspecialchars($metaData['description']) . '">';
        }

        if (! empty($metaData['keywords'])) {
            $html[] = '<meta name="keywords" content="' . htmlspecialchars($metaData['keywords']) . '">';
        }

        // Open Graph tags
        $ogTags = ['og_title', 'og_description', 'og_image', 'og_url', 'og_type'];
        foreach ($ogTags as $tag) {
            if (! empty($metaData[$tag])) {
                $property = str_replace('_', ':', $tag);
                $html[] = '<meta property="' . $property . '" content="' . htmlspecialchars($metaData[$tag]) . '">';
            }
        }

        // Twitter tags
        $twitterTags = ['twitter_card', 'twitter_title', 'twitter_description', 'twitter_image', 'twitter_site'];
        foreach ($twitterTags as $tag) {
            if (! empty($metaData[$tag])) {
                $property = str_replace('_', ':', $tag);
                $html[] = '<meta name="' . $property . '" content="' . htmlspecialchars($metaData[$tag]) . '">';
            }
        }

        // Article meta tags
        $articleTags = ['article_author', 'article_published_time', 'article_modified_time', 'article_section'];
        foreach ($articleTags as $tag) {
            if (! empty($metaData[$tag])) {
                $property = str_replace('_', ':', $tag);
                $html[] = '<meta property="' . $property . '" content="' . htmlspecialchars($metaData[$tag]) . '">';
            }
        }

        return implode("\n    ", $html);
    }
}
