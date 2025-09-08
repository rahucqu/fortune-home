<?php

<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\SeoSetting;
use App\Models\Tag;
use App\Services\SeoService;

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
    
    expect($generalSettings)->toBeInstanceOf(\Illuminate\Support\Collection::class)
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

it('can generate breadcrumb JSON-LD', function () {
    $breadcrumbs = [
        ['name' => 'Home', 'url' => '/'],
        ['name' => 'Blog', 'url' => '/blog'],
        ['name' => 'Post Title', 'url' => '/blog/post-title'],
    ];
    
    $seoService = new SeoService();
    $jsonLd = $seoService->generateBreadcrumbJsonLd($breadcrumbs);
    
    expect($jsonLd)
        ->toHaveKey('@context')
        ->toHaveKey('@type')
        ->toHaveKey('itemListElement')
        ->and($jsonLd['@context'])->toBe('https://schema.org')
        ->and($jsonLd['@type'])->toBe('BreadcrumbList')
        ->and($jsonLd['itemListElement'])->toHaveCount(3);
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

it('clears cache when settings are updated', function () {
    SeoSetting::set('cache_test', 'original');
    
    $original = SeoSetting::get('cache_test');
    
    SeoSetting::set('cache_test', 'updated');
    
    $updated = SeoSetting::get('cache_test');
    
    expect($original)->toBe('original')
        ->and($updated)->toBe('updated');
});
