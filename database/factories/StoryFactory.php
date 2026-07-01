<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StoryStatus;
use App\Models\Category;
use App\Models\Patient;
use App\Models\Story;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StoryFactory extends Factory
{
    protected $model = Story::class;

    public function definition(): array
    {
        $titles = [
            'A Journey to Recovery: Ahmed\'s Story',
            'How Community Support Saved My Family',
            'From Darkness to Light: My Battle with Illness',
            'Together We Can Make a Difference',
            'A Mother\'s Fight for Her Child\'s Health',
            'Rebuilding Lives After the Crisis',
            'Your Donation Changed Everything',
            'Hope in the Midst of Adversity',
            'The Power of Collective Giving',
            'A Second Chance at Life',
        ];

        return [
            'uuid' => Str::uuid(),
            'patient_id' => Patient::factory(),
            'category_id' => Category::factory(),
            'title' => [
                'en' => fake()->randomElement($titles),
                'ar' => fake('ar_SA')->sentence(5),
            ],
            'content' => [
                'en' => fake()->paragraphs(8, true),
                'ar' => fake('ar_SA')->paragraphs(8, true),
            ],
            'cover_image' => null,
            'status' => StoryStatus::APPROVED,
            'is_fundraising' => fake()->boolean(40),
            'target_amount' => fake()->randomFloat(2, 1000, 100000),
        ];
    }

    public function fundraising(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_fundraising' => true,
            'target_amount' => fake()->randomFloat(2, 5000, 100000),
        ]);
    }
}
