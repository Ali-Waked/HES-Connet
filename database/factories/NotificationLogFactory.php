<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationLogFactory extends Factory
{
    protected $model = NotificationLog::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['appointment_reminder', 'donation_received', 'article_published', 'story_approved', 'review_received']),
            'user_id' => User::factory(),
            'channel' => fake()->randomElement(['email', 'push', 'sms']),
            'status' => fake()->randomElement(['sent', 'sent', 'sent', 'failed', 'pending']),
            'payload' => [
                'subject' => fake()->sentence(4),
                'message' => fake()->paragraph(),
            ],
        ];
    }
}
