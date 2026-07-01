<?php

declare(strict_types=1);

namespace App\Notifications\Donations;

use App\Models\Donation;
use App\Notifications\BaseNotification;

class DonationMadeNotification extends BaseNotification
{
    public static function forAdmin(Donation $donation, ?string $locale = null): static
    {
        return new static(
            event: 'donation.made',
            role: 'admin',
            data: [
                'name' => $donation->donor?->name ?? 'Anonymous',
                'amount' => $donation->formatted_amount ?? number_format((float) $donation->amount, 2),
                'campaign' => $donation->campaign?->title ?? 'General',
                'action_text' => 'View Donation',
                'action_url' => route('donations.show', $donation),
            ],
            locale: $locale,
        );
    }

    public static function forEvent(string $donorName, float $amount, ?string $campaign, ?string $locale = null): static
    {
        return new static(
            event: 'donation.made',
            role: 'admin',
            data: [
                'name' => $donorName,
                'amount' => number_format($amount, 2),
                'campaign' => $campaign ?? 'General',
                'action_text' => 'View Donation',
                'action_url' => route('donations.index'),
            ],
            locale: $locale,
        );
    }
}
