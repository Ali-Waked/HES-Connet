<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DonationStatus;
use App\Enums\PaymentStatus;
use App\Models\Donation;
use App\Models\Payment;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DonationSeeder extends Seeder
{
    public function run(): void
    {
        $stories = Story::where('is_fundraising', true)->get();
        $donorIds = User::pluck('id')->toArray();

        if ($stories->isEmpty()) {
            return;
        }

        foreach ($stories as $story) {
            $numDonations = fake()->numberBetween(3, 10);
            for ($i = 0; $i < $numDonations; $i++) {
                $amount = fake()->randomFloat(2, 10, 2000);
                $status = fake()->randomElement([
                    DonationStatus::COMPLETED,
                    DonationStatus::COMPLETED,
                    DonationStatus::COMPLETED,
                    DonationStatus::PENDING,
                ]);

                $donation = Donation::create([
                    'uuid' => Str::uuid(),
                    'story_id' => $story->id,
                    'donor_id' => $donorIds[array_rand($donorIds)],
                    'amount' => $amount,
                    'currency' => 'USD',
                    'status' => $status,
                ]);

                if ($status === DonationStatus::COMPLETED) {
                    Payment::create([
                        'uuid' => Str::uuid(),
                        'donation_id' => $donation->id,
                        'payable_type' => 'donation',
                        'payable_id' => $donation->id,
                        'provider' => 'stripe',
                        'transaction_ref' => 'pi_'.Str::random(24),
                        'amount' => $amount,
                        'currency' => 'USD',
                        'status' => PaymentStatus::PAID,
                        'response_json' => [
                            'id' => 'ch_'.Str::random(24),
                            'object' => 'charge',
                            'amount' => (int) ($amount * 100),
                            'currency' => 'usd',
                            'status' => 'succeeded',
                        ],
                    ]);
                }
            }
        }

        // Collected amount is now computed dynamically from the donations table
    }
}
