<?php

declare(strict_types=1);

namespace App\Notifications\Reviews;

use App\Models\PlatformReview;
use App\Notifications\BaseNotification;

class PlatformReviewSubmittedNotification extends BaseNotification
{
    public static function forAdmin(PlatformReview $platformReview, ?string $locale = null): static
    {
        return new static(
            event: 'platform.review.submitted',
            role: 'admin',
            data: [
                'user' => $platformReview->user?->name ?? 'A user',
                'rating' => $platformReview->rating,
                'action_text' => 'View Review',
                'action_url' => route('dashboard.platform-reviews.show', $platformReview),
            ],
            locale: $locale,
        );
    }
}
