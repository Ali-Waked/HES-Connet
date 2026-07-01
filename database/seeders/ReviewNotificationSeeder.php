<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PlatformReview;
use App\Models\ReviewNotification;
use Illuminate\Database\Seeder;

class ReviewNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = PlatformReview::all();

        if ($reviews->isEmpty()) {
            return;
        }

        $adminEmail = 'admin@gmail.com';

        foreach ($reviews as $review) {
            ReviewNotification::create([
                'review_id' => $review->id,
                'type' => fake()->randomElement(['auto_reply', 'admin_reply']),
                'sent_to' => $adminEmail,
                'sent_at' => $review->created_at ?? now()->subHours(fake()->numberBetween(1, 72)),
                'payload' => [
                    'review_rating' => $review->rating,
                    'review_comment' => $review->comment,
                    'notified_at' => now()->toDateTimeString(),
                ],
            ]);
        }
    }
}
