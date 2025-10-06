<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlogPost>
 */
class BlogPostFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $title = $this->generateBlogTitle();
        $content = $this->generateBlogContent($title);
        $excerpt = $this->generateExcerpt($content);

        $createdAt = fake()->dateTimeBetween('-2 years', 'now');
        $isPublished = fake()->boolean(70); // 70% published

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'excerpt' => $excerpt,
            'content' => $content,
            // 'featured_image_path' => fake()->boolean(60) ? 'blog/images/'.fake()->uuid().'.jpg' : null,
            'meta_title' => $title,
            'meta_description' => Str::limit($excerpt, 160),
            'author_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'category_id' => BlogCategory::inRandomOrder()->first()?->id ?? BlogCategory::factory(),
            'status' => $isPublished ? 'published' : fake()->randomElement(['draft', 'draft', 'archived']),
            'is_featured' => fake()->boolean(20), // 20% featured
            'views_count' => $isPublished ? fake()->numberBetween(10, 5000) : 0,
            'published_at' => $isPublished ? fake()->dateTimeBetween($createdAt, 'now') : null,
            'created_at' => $createdAt,
        ];
    }

    /**
     * Generate realistic blog titles for real estate.
     */
    private function generateBlogTitle(): string
    {
        $templates = [
            // Tips and advice
            'X Essential Tips for {action}',
            'The Ultimate Guide to {topic}',
            'How to {action} in {timeframe}',
            '{number} Mistakes to Avoid When {action}',
            'Why {topic} is Important for {audience}',
            'The Complete {audience} Guide to {topic}',

            // Market and trends
            '{year} {location} Real Estate Market Trends',
            'What to Expect from the {location} Housing Market',
            'Is Now the Right Time to {action}?',
            '{location} Neighborhood Spotlight: {area}',
            'Market Analysis: {topic} in {year}',

            // Investment and finance
            'Understanding {topic} for Real Estate Investment',
            'How to Finance Your {propertyType} Purchase',
            'The ROI of {topic}',
            'Investment Strategies for {audience}',

            // Home improvement
            '{number} Ways to Increase Your Home\'s Value',
            'Budget-Friendly {topic} Ideas',
            'Before You {action}: What You Need to Know',
            'Staging Your Home for a Quick Sale',

            // Technology and innovation
            'How Technology is Changing {topic}',
            'Virtual Tours vs Traditional Showings',
            'The Future of {topic}',
        ];

        $template = fake()->randomElement($templates);

        $replacements = [
            '{action}' => fake()->randomElement([
                'Buying Your First Home', 'Selling Your Property', 'Investing in Real Estate',
                'Finding the Perfect Neighborhood', 'Negotiating Price', 'Getting Pre-Approved',
                'Staging Your Home', 'Working with Agents',
            ]),
            '{topic}' => fake()->randomElement([
                'Home Inspections', 'Property Taxes', 'HOA Fees', 'Mortgage Rates',
                'Closing Costs', 'Property Insurance', 'Market Valuation', 'Real Estate Photography',
                'Curb Appeal', 'Energy Efficiency', 'Smart Home Technology',
            ]),
            '{audience}' => fake()->randomElement([
                'First-Time Buyer', 'Investor', 'Seller', 'Homeowner', 'Real Estate Agent',
            ]),
            '{propertyType}' => fake()->randomElement([
                'Condo', 'Townhouse', 'Single-Family Home', 'Investment Property', 'Commercial Property',
            ]),
            '{location}' => fake()->randomElement([
                'California', 'Texas', 'Florida', 'New York', 'Chicago', 'Phoenix', 'Seattle', 'Denver',
            ]),
            '{area}' => fake()->randomElement([
                'Downtown District', 'Suburban Haven', 'Historic Quarter', 'Waterfront Community',
            ]),
            '{timeframe}' => fake()->randomElement([
                '2024', '30 Days', 'This Market', 'Any Market',
            ]),
            '{number}' => fake()->randomElement(['5', '7', '10', '15']),
            '{year}' => date('Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Generate realistic blog content.
     */
    private function generateBlogContent(string $title): string
    {
        $introduction = $this->generateIntroduction($title);
        $sections = $this->generateContentSections();
        $conclusion = $this->generateConclusion();

        return $introduction."\n\n".implode("\n\n", $sections)."\n\n".$conclusion;
    }

    /**
     * Generate introduction paragraph.
     */
    private function generateIntroduction(string $title): string
    {
        $intros = [
            'The real estate market is constantly evolving, and staying informed is crucial for making smart decisions.',
            "Whether you're a first-time buyer or seasoned investor, understanding the current market trends can make all the difference.",
            'Navigating the real estate world can be complex, but with the right knowledge and preparation, you can achieve your goals.',
            'Real estate investment requires careful planning and strategic thinking to maximize your returns.',
            "The housing market presents both opportunities and challenges for today's buyers and sellers.",
        ];

        return fake()->randomElement($intros).' '.fake()->paragraph(3);
    }

    /**
     * Generate content sections.
     */
    private function generateContentSections(): array
    {
        $sectionCount = fake()->numberBetween(3, 6);
        $sections = [];

        for ($i = 0; $i < $sectionCount; $i++) {
            $heading = $this->generateSectionHeading($i + 1);
            $content = fake()->paragraphs(fake()->numberBetween(2, 4), true);
            $sections[] = "## {$heading}\n\n{$content}";
        }

        return $sections;
    }

    /**
     * Generate section headings.
     */
    private function generateSectionHeading(int $number): string
    {
        $headings = [
            'Understanding the Market',
            'Key Factors to Consider',
            'Financial Preparation',
            'Working with Professionals',
            'Common Pitfalls to Avoid',
            'Next Steps',
            'Market Trends',
            'Investment Strategies',
            'Legal Considerations',
            'Timeline and Process',
        ];

        return fake()->randomElement($headings);
    }

    /**
     * Generate conclusion paragraph.
     */
    private function generateConclusion(): string
    {
        $conclusions = [
            'Real estate decisions are significant investments that require careful consideration and professional guidance.',
            "By following these guidelines and staying informed about market trends, you'll be better positioned for success.",
            "Remember that every real estate transaction is unique, and it's important to work with experienced professionals.",
            'The real estate market offers opportunities for those who are prepared and make informed decisions.',
        ];

        return fake()->randomElement($conclusions).' '.fake()->paragraph(2);
    }

    /**
     * Generate excerpt from content.
     */
    private function generateExcerpt(string $content): string
    {
        $plainText = strip_tags($content);

        return Str::limit($plainText, 200);
    }

    /**
     * Create a published blog post.
     */
    public function published(): static
    {
        return $this->state(function (array $attributes) {
            $publishedAt = fake()->dateTimeBetween($attributes['created_at'] ?? '-1 year', 'now');

            return [
                'status' => 'published',
                'published_at' => $publishedAt,
                'views_count' => fake()->numberBetween(50, 3000),
            ];
        });
    }

    /**
     * Create a draft blog post.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
            'views_count' => 0,
        ]);
    }

    /**
     * Create a featured blog post.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Create a blog post for a specific author.
     */
    public function byAuthor(User $author): static
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => $author->id,
        ]);
    }

    /**
     * Create a blog post in a specific category.
     */
    public function inCategory(BlogCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }
}
