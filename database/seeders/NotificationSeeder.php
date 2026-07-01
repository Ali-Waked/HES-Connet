<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();

        $notifications = [
            ['type' => 'appointment_reminder', 'channel' => 'email', 'subject' => 'Appointment Reminder'],
            ['type' => 'donation_received', 'channel' => 'email', 'subject' => 'Donation Received'],
            ['type' => 'article_published', 'channel' => 'push', 'subject' => 'New Article Published'],
            ['type' => 'story_approved', 'channel' => 'email', 'subject' => 'Your Story Has Been Approved'],
            ['type' => 'review_received', 'channel' => 'email', 'subject' => 'New Review Received'],
            ['type' => 'welcome', 'channel' => 'email', 'subject' => 'Welcome to Health Ecosystem'],
            ['type' => 'password_reset', 'channel' => 'email', 'subject' => 'Password Reset Request'],
            ['type' => 'account_verified', 'channel' => 'email', 'subject' => 'Account Verified'],
            ['type' => 'job_application', 'channel' => 'email', 'subject' => 'New Job Application'],
            ['type' => 'subscription_confirmed', 'channel' => 'email', 'subject' => 'Subscription Confirmed'],
        ];

        foreach ($notifications as $data) {
            $numRecipients = fake()->numberBetween(5, 15);
            for ($i = 0; $i < $numRecipients; $i++) {
                NotificationLog::create([
                    'type' => $data['type'],
                    'user_id' => $userIds[array_rand($userIds)],
                    'channel' => $data['channel'],
                    'status' => fake()->randomElement(['sent', 'sent', 'sent', 'delivered', 'failed']),
                    'payload' => [
                        'subject' => $data['subject'],
                        'message' => fake()->sentence(10),
                        'action_url' => fake()->url(),
                    ],
                ]);
            }
        }
    }
}
