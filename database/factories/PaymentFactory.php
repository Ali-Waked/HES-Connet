<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Donation;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'donation_id' => Donation::factory(),
            'payable_type' => 'donation',
            'payable_id' => Donation::factory(),
            'provider' => 'stripe',
            'transaction_ref' => 'pi_'.Str::random(24),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'currency' => 'USD',
            'status' => fake()->randomElement([PaymentStatus::PAID, PaymentStatus::PAID, PaymentStatus::PAID, PaymentStatus::PENDING, PaymentStatus::FAILED]),
            'response_json' => ['id' => 'ch_'.Str::random(24), 'object' => 'charge', 'status' => 'succeeded'],
        ];
    }
}
