<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Post;
use App\Models\SeoSetting;
use App\Models\Tag;
use App\Services\SeoService;
use Illuminate\Support\Collection;

beforeEach(function () {
    // Seed basic SEO settings for tests
    $defaults = SeoSetting::getDefaults();

    foreach ($defaults as $key => $config) {
        SeoSetting::create([
            'key' => $key,
            'value' => $config['value'],
            'type' => $config['type'],
            'group' => $config['group'],
            'description' => $config['description'],
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }
});

it('can get and set SEO settings', function () {
    SeoSetting::set('test_setting', 'test_value');

    expect(SeoSetting::get('test_setting'))->toBe('test_value');
});

it('can get settings by group', function () {
    $generalSettings = SeoSetting::getByGroup('general');

    expect($generalSettings)->toBeInstanceOf(Collection::class)
        ->and($generalSettings->has('site_title'))->toBeTrue()
        ->and($generalSettings->has('site_description'))->toBeTrue();
});

it('can generate post SEO meta', function () {
    $category = Category::factory()->create(['name' => 'Technology']);
    $tag = Tag::factory()->create(['name' => 'Laravel']);

    $post = Post::factory()
        ->published()
        ->create([
            'title' => 'Test Post',
            'excerpt' => 'This is a test post excerpt.',
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => '', // Explicitly empty to force auto-generation
            'category_id' => $category->id,
        ]);

    $post->tags()->attach($tag);
    $post->load(['category', 'tags', 'user']);

    $seoService = new SeoService();
    $meta = $seoService->generatePostMeta($post);

    expect($meta)
        ->toHaveKey('title')
        ->toHaveKey('description')
        ->toHaveKey('keywords')
        ->toHaveKey('og_title')
        ->toHaveKey('og_description')
        ->and($meta['meta_title'])->toBe('Test Post')
        ->and($meta['description'])->toBe('This is a test post excerpt.')
        ->and($meta['keywords'])->toContain('Technology')
        ->and($meta['keywords'])->toContain('Laravel');
});

it('can generate category SEO meta', function () {
    $category = Category::factory()->create([
        'name' => 'Technology',
        'description' => 'Technology related posts',
        'seo_title' => null,
        'seo_description' => null,
    ]);

    $seoService = new SeoService();
    $meta = $seoService->generateCategoryMeta($category);

    expect($meta)
        ->toHaveKey('title')
        ->toHaveKey('description')
        ->toHaveKey('og_title')
        ->and($meta['meta_title'])->toBe('Technology')
        ->and($meta['description'])->toBe('Technology related posts');
});

it('can generate tag SEO meta', function () {
    $tag = Tag::factory()->create([
        'name' => 'Laravel',
        'description' => 'Laravel framework posts',
        'seo_title' => null,
        'seo_description' => null,
    ]);

    $seoService = new SeoService();
    $meta = $seoService->generateTagMeta($tag);

    expect($meta)
        ->toHaveKey('title')
        ->toHaveKey('description')
        ->toHaveKey('og_title')
        ->and($meta['meta_title'])->toBe('Laravel')
        ->and($meta['description'])->toBe('Laravel framework posts');
});

it('can generate homepage SEO meta', function () {
    $seoService = new SeoService();
    $meta = $seoService->generateHomepageMeta();

    expect($meta)
        ->toHaveKey('title')
        ->toHaveKey('description')
        ->toHaveKey('og_title')
        ->and($meta['title'])->not->toBeEmpty();
});

it('can generate JSON-LD for post', function () {
    $post = Post::factory()
        ->published()
        ->create([
            'title' => 'Test Post',
            'content' => 'This is test content.',
        ]);

    $post->load(['user', 'category', 'tags']);

    $seoService = new SeoService();
    $jsonLd = $seoService->generatePostJsonLd($post);

    expect($jsonLd)
        ->toHaveKey('@context')
        ->toHaveKey('@type')
        ->toHaveKey('headline')
        ->and($jsonLd['@context'])->toBe('https://schema.org')
        ->and($jsonLd['@type'])->toBe('Article')
        ->and($jsonLd['headline'])->toBe('Test Post');
});

it('can generate meta tags HTML', function () {
    $metaData = [
        'description' => 'Test description',
        'keywords' => 'test, keywords',
        'og_title' => 'Test Title',
        'og_description' => 'Test OG description',
        'twitter_card' => 'summary',
    ];

    $seoService = new SeoService();
    $html = $seoService->generateMetaTagsHtml($metaData);

    expect($html)
        ->toContain('meta name="description"')
        ->toContain('meta name="keywords"')
        ->toContain('meta property="og:title"')
        ->toContain('meta name="twitter:card"');
});

it('caches SEO settings properly', function () {
    SeoSetting::set('cached_setting', 'cached_value');

    // First call should hit database
    $value1 = SeoSetting::get('cached_setting');

    // Second call should hit cache
    $value2 = SeoSetting::get('cached_setting');

    expect($value1)->toBe('cached_value')
        ->and($value2)->toBe('cached_value');
});
