<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Models\Profession;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'user_id' => User::factory(),
            'profession_id' => fn () => Profession::inRandomOrder()->first()?->id ?? Profession::factory(),
            'specialization' => [
                'en' => fake()->randomElement(['Cardiology', 'Pediatrics', 'Neurology', 'Orthopedics', 'Dermatology', 'Ophthalmology', 'General Surgery', 'Internal Medicine']),
                'ar' => fake('ar_SA')->randomElement(['قلب', 'أطفال', 'أعصاب', 'عظام', 'جلدية', 'عيون', 'جراحة عامة', 'باطنة']),
            ],
            'experience_years' => fake()->numberBetween(1, 30),
            'bio' => [
                'en' => fake()->paragraph(),
                'ar' => fake('ar_SA')->paragraph(),
            ],
            'consultation_fee' => fake()->randomFloat(2, 20, 200),
            'status' => AccountStatus::ACTIVE,
            'staff_position_id' => fn () => StaffPosition::inRandomOrder()->first()?->id ?? StaffPosition::factory(),
        ];
    }

    public function doctor(): static
    {
        return $this->state(fn (array $attributes) => [
            'profession_id' => Profession::where('slug', 'doctor')->first()?->id ?? Profession::factory(),
        ]);
    }
}
