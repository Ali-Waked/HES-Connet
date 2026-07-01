<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CategoriesType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $types = [CategoriesType::ARTICLE, CategoriesType::STORY, CategoriesType::JOB];

        return [
            'uuid' => Str::uuid(),
            'name' => [
                'en' => fake()->word(),
                'ar' => fake('ar_SA')->word(),
            ],
            'description' => [
                'en' => fake()->sentence(),
                'ar' => fake('ar_SA')->sentence(),
            ],
            'type' => fake()->randomElement($types),
            'is_active' => true,
        ];
    }

    public function articles(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CategoriesType::ARTICLE,
        ]);
    }

    public function stories(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CategoriesType::STORY,
        ]);
    }

    public function jobs(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CategoriesType::JOB,
        ]);
    }
}
