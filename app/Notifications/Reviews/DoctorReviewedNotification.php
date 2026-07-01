<?php

declare(strict_types=1);

namespace App\Notifications\Reviews;

use App\Models\Review;
use App\Notifications\BaseNotification;

class DoctorReviewedNotification extends BaseNotification
{
    public static function forDoctor(Review $review, ?string $locale = null): static
    {
        return new static(
            event: 'doctor.reviewed',
            role: 'doctor',
            data: [
                'doctor' => $review->appointment?->facilityStaff?->staff?->user?->name ?? 'Doctor',
                'patient' => $review->patient?->user?->name ?? 'A patient',
                'rating' => $review->rating,
                'action_text' => 'View Review',
                'action_url' => route('dashboard.reviews.show', $review),
            ],
            locale: $locale,
        );
    }

    public static function forAdmin(Review $review, ?string $locale = null): static
    {
        return new static(
            event: 'doctor.reviewed',
            role: 'admin',
            data: [
                'doctor' => $review->appointment?->facilityStaff?->staff?->user?->name ?? 'Doctor',
                'patient' => $review->patient?->user?->name ?? 'A patient',
                'rating' => $review->rating,
                'action_text' => 'View Review',
                'action_url' => route('dashboard.reviews.show', $review),
            ],
            locale: $locale,
        );
    }
}
