<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $tags = [
            'Health', 'Wellness', 'Nutrition', 'Fitness', 'Mental Health',
            'Prevention', 'Treatment', 'Vaccination', 'Pediatrics', 'Cardiology',
            'Diabetes', 'Cancer', 'Surgery', 'Emergency', 'Pharmacy',
            'Pregnancy', 'Elderly Care', 'Public Health', 'Research', 'Technology',
        ];

        $tag = fake()->randomElement($tags);

        return [
            'uuid' => Str::uuid(),
            'name' => [
                'en' => $tag,
                'ar' => fake('ar_SA')->word(),
            ],
        ];
    }
}
