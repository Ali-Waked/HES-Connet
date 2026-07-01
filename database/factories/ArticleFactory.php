<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $titles = [
            'The Importance of Regular Health Checkups',
            'Understanding Blood Pressure: A Complete Guide',
            'How to Boost Your Immune System Naturally',
            'Mental Health Awareness in the Workplace',
            'The Role of Nutrition in Disease Prevention',
            'Managing Diabetes: Tips for a Healthy Lifestyle',
            'Children\'s Vaccination: What Parents Need to Know',
            'The Benefits of Regular Exercise for Heart Health',
            'Understanding Cancer: Early Detection Saves Lives',
            'Sleep Hygiene: How to Improve Your Sleep Quality',
        ];

        $title = fake()->randomElement($titles);

        return [
            'uuid' => Str::uuid(),
            'author_id' => User::factory(),
            'title' => [
                'en' => $title,
                'ar' => fake('ar_SA')->sentence(6),
            ],
            'content' => [
                'en' => fake()->paragraphs(5, true),
                'ar' => fake('ar_SA')->paragraphs(5, true),
            ],
            'category_id' => Category::factory(),
            'cover_image' => null,
            'status' => ArticleStatus::PUBLISHED,
            'views' => fake()->numberBetween(0, 5000),
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ArticleStatus::PUBLISHED,
            'published_at' => now()->subDays(fake()->numberBetween(1, 180)),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ArticleStatus::DRAFT,
            'published_at' => null,
        ]);
    }
}
