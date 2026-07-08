<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Specialization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SpecializationFactory extends Factory
{
    protected $model = Specialization::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => [
                'en' => fake()->randomElement(['Cardiology', 'Pediatrics', 'Neurology', 'Orthopedics', 'Dermatology', 'Ophthalmology', 'General Surgery', 'Internal Medicine']),
                'ar' => fake('ar_SA')->randomElement(['قلب', 'أطفال', 'أعصاب', 'عظام', 'جلدية', 'عيون', 'جراحة عامة', 'باطنة']),
            ],
            'description' => [
                'en' => fake()->sentence(),
                'ar' => fake('ar_SA')->sentence(),
            ],
        ];
    }
}
