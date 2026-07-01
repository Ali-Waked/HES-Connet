<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PlatformReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlatformReviewSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();

        $reviews = [
            ['rating' => 5, 'comment' => 'Great platform! Very easy to find doctors and book appointments.', 'status' => 'approved', 'is_featured' => true],
            ['rating' => 4, 'comment' => 'Very useful for managing my family\'s healthcare needs.', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 5, 'comment' => 'The platform has improved significantly over time. Highly recommended.', 'status' => 'approved', 'is_featured' => true],
            ['rating' => 3, 'comment' => 'Good service but could use more features and better navigation.', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 5, 'comment' => 'Excellent resource for the community. So glad this exists.', 'status' => 'approved', 'is_featured' => true],
            ['rating' => 4, 'comment' => 'I love how easy it is to book appointments online. Saves so much time.', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 4, 'comment' => 'Helpful platform but needs more doctors in my area.', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 5, 'comment' => 'Outstanding! This has made healthcare access so much easier for everyone.', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 4, 'comment' => 'Good concept with great potential. Looking forward to future updates.', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 5, 'comment' => 'Very user-friendly and reliable platform. Never had any issues.', 'status' => 'approved', 'is_featured' => true],
            ['rating' => 2, 'comment' => 'The platform is okay but needs improvement in search functionality.', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 4, 'comment' => 'Great initiative! This is exactly what our healthcare system needed.', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 5, 'comment' => 'Amazing platform with a wonderful team behind it. Keep it up!', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 3, 'comment' => 'Decent platform but appointment reminders could be better.', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 5, 'comment' => 'Life-changing platform. It connected me with the right doctor quickly.', 'status' => 'approved', 'is_featured' => true],
            ['rating' => 4, 'comment' => 'Very helpful for finding specialists in my area.', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 1, 'comment' => 'Experienced some technical issues but support team was helpful.', 'status' => 'pending', 'is_featured' => false],
            ['rating' => 5, 'comment' => 'The best healthcare platform I have ever used. Five stars!', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 4, 'comment' => 'Convenient and reliable. I recommend it to all my friends and family.', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 3, 'comment' => 'Good platform overall but needs more payment options.', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 5, 'comment' => 'Excellent service! The team is very responsive and helpful.', 'status' => 'approved', 'is_featured' => false],
            ['rating' => 4, 'comment' => 'Very satisfied with the platform. Makes healthcare accessible.', 'status' => 'rejected', 'is_featured' => false],
        ];

        $usedUserIds = [];

        foreach ($reviews as $index => $data) {
            $available = array_values(array_diff($userIds, $usedUserIds));
            if (empty($available)) {
                break;
            }
            $userId = $available[array_rand($available)];
            $usedUserIds[] = $userId;

            $review = PlatformReview::create([
                'user_id' => $userId,
                'rating' => $data['rating'],
                'comment' => $data['comment'],
                'reply' => $data['is_featured'] ? 'Thank you for your valuable feedback! We appreciate your support.' : null,
                'replied_by' => $data['is_featured'] ? User::where('email', 'admin@gmail.com')->first()?->id : null,
                'replied_at' => $data['is_featured'] ? now()->subDays(fake()->numberBetween(1, 30)) : null,
                'is_featured' => $data['is_featured'],
                'status' => $data['status'],
            ]);
        }
    }
}
