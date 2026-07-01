<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlatformReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlatformReviewFactory extends Factory
{
    protected $model = PlatformReview::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->randomElement([
                'Great platform! Very easy to find doctors and book appointments.',
                'Very useful for managing my family\'s healthcare needs.',
                'The platform has improved significantly over time.',
                'Good service but could use more features.',
                'Excellent resource for the community.',
                'I love how easy it is to book appointments online.',
                'Helpful platform but needs more doctors in my area.',
                'Outstanding! This has made healthcare access so much easier.',
                'Good concept with great potential.',
                'Very user-friendly and reliable platform.',
            ]),
            'admin_reply' => null,
            'replied_by' => null,
            'replied_at' => null,
            'is_featured' => fake()->boolean(10),
            'status' => fake()->randomElement(['published', 'published', 'pending', 'hidden']),
        ];
    }
}
