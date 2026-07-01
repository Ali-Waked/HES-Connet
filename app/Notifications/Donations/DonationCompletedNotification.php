<?php

declare(strict_types=1);

namespace App\Notifications\Donations;

use App\Models\Donation;
use App\Notifications\BaseNotification;

class DonationCompletedNotification extends BaseNotification
{
    public static function forDonor(Donation $donation, ?string $locale = null): static
    {
        $locale = $locale ?? app()->getLocale();
        $storyTitle = self::storyTitle($donation, $locale);

        return new static(
            event: 'donation.completed.donor',
            role: 'donor',
            data: [
                'amount' => number_format((float) $donation->amount, 2),
                'story' => $storyTitle,
                'action_text' => 'View Details',
                'action_url' => route('donations.show', $donation),
            ],
            locale: $locale,
        );
    }

    public static function forPatient(Donation $donation, ?string $locale = null): static
    {
        $locale = $locale ?? app()->getLocale();
        $storyTitle = self::storyTitle($donation, $locale);

        return new static(
            event: 'donation.completed.patient',
            role: 'patient',
            data: [
                'amount' => number_format((float) $donation->amount, 2),
                'story' => $storyTitle,
                'action_text' => 'View Story',
                'action_url' => $donation->story ? route('stories.show', $donation->story) : null,
            ],
            locale: $locale,
        );
    }

    public static function forAdmin(Donation $donation, ?string $locale = null): static
    {
        $locale = $locale ?? app()->getLocale();
        $storyTitle = self::storyTitle($donation, $locale);

        return new static(
            event: 'donation.completed.admin',
            role: 'admin',
            data: [
                'name' => $donation->donor?->name ?? 'Anonymous',
                'amount' => number_format((float) $donation->amount, 2),
                'story' => $storyTitle,
                'action_text' => 'View Donation',
                'action_url' => route('donations.show', $donation),
            ],
            locale: $locale,
        );
    }

    private static function storyTitle(Donation $donation, string $locale): string
    {
        $story = $donation->story;

        if (! $story) {
            return 'General';
        }

        $translations = $story->getTranslations('title');

        return $translations[$locale] ?? $translations['en'] ?? 'Healthcare Story';
    }
}
