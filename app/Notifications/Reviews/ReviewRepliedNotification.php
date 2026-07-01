<?php

declare(strict_types=1);

namespace App\Notifications\Reviews;

use App\Models\ReviewReply;
use App\Notifications\BaseNotification;

class ReviewRepliedNotification extends BaseNotification
{
    public static function forPatient(ReviewReply $reviewReply, ?string $locale = null): static
    {
        return new static(
            event: 'review.replied',
            role: 'patient',
            data: [
                'doctor' => $reviewReply->review?->appointment?->facilityStaff?->staff?->user?->name ?? 'Doctor',
                'reply' => $reviewReply->reply ?? '',
                'action_text' => 'View Review',
                'action_url' => route('dashboard.reviews.show', $reviewReply->review),
            ],
            locale: $locale,
        );
    }
}
