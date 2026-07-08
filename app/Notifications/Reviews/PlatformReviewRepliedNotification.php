<?php

declare(strict_types=1);

namespace App\Notifications\Reviews;

use App\Models\PlatformReview;
use App\Notifications\BaseNotification;

class PlatformReviewRepliedNotification extends BaseNotification
{
    public static function forOwner(PlatformReview $platformReview, ?string $locale = null): static
    {
        return new static(
            event: 'platform.review.replied',
            role: 'owner',
            data: [
                'user' => $platformReview->user?->name ?? 'A user',
                'reply' => $platformReview->reply ?? '',
                'action_text' => 'View Review',
                'action_url' => route('platform-reviews.show', $platformReview),
            ],
            locale: $locale,
        );
    }
}
