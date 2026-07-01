<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        $types = ['government', 'un_agency', 'international_ngo', 'local_ngo', 'private'];

        return [
            'uuid' => Str::uuid(),
            'name' => [
                'en' => fake()->company(),
                'ar' => fake('ar_SA')->company(),
            ],
            'description' => [
                'en' => fake()->sentence(),
                'ar' => fake('ar_SA')->sentence(),
            ],
            'type' => fake()->randomElement($types),
        ];
    }
}
