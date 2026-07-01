<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'story_id' => Story::factory(),
            'donor_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'currency' => 'USD',
            'status' => fake()->randomElement([DonationStatus::COMPLETED, DonationStatus::COMPLETED, DonationStatus::COMPLETED, DonationStatus::PENDING, DonationStatus::FAILED]),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DonationStatus::COMPLETED,
        ]);
    }
}
