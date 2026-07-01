<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'user_id' => User::factory(),
            'locale' => fake()->randomElement(['en', 'ar']),
            'verified_at' => fake()->boolean(70) ? now()->subDays(fake()->numberBetween(1, 90)) : null,
            'is_active' => fake()->boolean(85),
            'unsubscribe_token' => Str::random(32),
        ];
    }
}
