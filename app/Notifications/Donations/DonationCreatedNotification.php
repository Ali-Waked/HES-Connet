<?php

declare(strict_types=1);

namespace App\Notifications\Donations;

use App\Models\Donation;
use App\Notifications\BaseNotification;

class DonationCreatedNotification extends BaseNotification
{
    public static function forAdmin(Donation $donation, ?string $locale = null): static
    {
        return new static(
            event: 'donation.created',
            role: 'admin',
            data: [
                'name' => $donation->donor?->name ?? 'Anonymous',
                'amount' => $donation->formatted_amount ?? number_format((float) $donation->amount, 2),
                'campaign' => $donation->story?->getTranslations('title')['en'] ?? $donation->story?->title ?? 'General',
                'action_text' => 'View Donation',
                'action_url' => route('donations.show', $donation),
            ],
            locale: $locale,
        );
    }
}
