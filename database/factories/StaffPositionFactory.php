<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StaffPosition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StaffPositionFactory extends Factory
{
    protected $model = StaffPosition::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => [
                'en' => fake()->jobTitle(),
                'ar' => fake('ar_SA')->jobTitle(),
            ],
            'description' => [
                'en' => fake()->sentence(),
                'ar' => fake('ar_SA')->sentence(),
            ],
            'is_active' => true,
        ];
    }
}
