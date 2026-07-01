<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => [
                'en' => fake()->word(),
                'ar' => fake('ar_SA')->word(),
            ],
            'slug' => Str::slug(fake()->unique()->word()),
            'scope' => fake()->randomElement(['system', 'facility']),
            'description' => [
                'en' => fake()->sentence(),
                'ar' => fake('ar_SA')->sentence(),
            ],
            'is_system' => fake()->boolean(30),
            'is_active' => true,
        ];
    }
}
