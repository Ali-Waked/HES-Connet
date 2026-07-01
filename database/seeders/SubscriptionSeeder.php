<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();

        $subscriptions = [
            ['email' => 'ahmad@example.com', 'locale' => 'ar'],
            ['email' => 'lina@example.com', 'locale' => 'en'],
            ['email' => 'fatima@example.com', 'locale' => 'ar'],
            ['email' => 'omar@example.com', 'locale' => 'ar'],
            ['email' => 'dina@example.com', 'locale' => 'en'],
        ];

        foreach ($subscriptions as $data) {
            $user = User::where('email', $data['email'])->first();

            $subscription = Subscription::create([
                'email' => $data['email'],
                'user_id' => $user?->id,
                'locale' => $data['locale'],
                'verified_at' => now()->subDays(fake()->numberBetween(1, 90)),
                'is_active' => true,
                'unsubscribe_token' => Str::random(32),
            ]);

            SubscriptionType::create(['subscription_id' => $subscription->id, 'type' => 'article']);
            SubscriptionType::create(['subscription_id' => $subscription->id, 'type' => 'story']);
            SubscriptionType::create(['subscription_id' => $subscription->id, 'type' => 'job']);
        }

        // Create additional random subscriptions
        Subscription::factory()->count(20)->create()->each(function (Subscription $sub) {
            $types = ['article', 'story', 'job', 'event', 'newsletter'];
            $numTypes = fake()->numberBetween(1, 3);
            $selectedTypes = (array) array_rand(array_flip($types), $numTypes);
            foreach ((array) $selectedTypes as $type) {
                SubscriptionType::create([
                    'subscription_id' => $sub->id,
                    'type' => $type,
                ]);
            }
        });
    }
}
