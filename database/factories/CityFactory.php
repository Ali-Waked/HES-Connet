<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => [
                'en' => fake()->city(),
                'ar' => fake('ar_SA')->city(),
            ],
            'is_active' => true,
        ];
    }
}
