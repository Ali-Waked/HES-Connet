<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Profession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProfessionFactory extends Factory
{
    protected $model = Profession::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => [
                'en' => fake()->jobTitle(),
                'ar' => fake('ar_SA')->jobTitle(),
            ],
            'slug' => Str::slug(fake()->unique()->jobTitle()),
            'description' => [
                'en' => fake()->sentence(),
                'ar' => fake('ar_SA')->sentence(),
            ],
            'is_active' => true,
        ];
    }
}
