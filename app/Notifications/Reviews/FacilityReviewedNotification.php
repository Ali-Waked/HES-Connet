<?php

declare(strict_types=1);

namespace App\Notifications\Reviews;

use App\Models\FacilityReview;
use App\Notifications\BaseNotification;

class FacilityReviewedNotification extends BaseNotification
{
    public static function forAdmin(FacilityReview $facilityReview, ?string $locale = null): static
    {
        return new static(
            event: 'facility.reviewed',
            role: 'admin',
            data: [
                'facility' => $facilityReview->facility?->name ?? 'A facility',
                'patient' => $facilityReview->patient?->user?->name ?? 'A patient',
                'rating' => $facilityReview->rating,
                'action_text' => 'View Review',
                'action_url' => route('dashboard.facility-reviews.show', $facilityReview),
            ],
            locale: $locale,
        );
    }

    public static function forFacilityAdmin(FacilityReview $facilityReview, ?string $locale = null): static
    {
        return new static(
            event: 'facility.reviewed',
            role: 'facility_admin',
            data: [
                'facility' => $facilityReview->facility?->name ?? 'A facility',
                'patient' => $facilityReview->patient?->user?->name ?? 'A patient',
                'rating' => $facilityReview->rating,
                'action_text' => 'View Review',
                'action_url' => route('dashboard.facility-reviews.show', $facilityReview),
            ],
            locale: $locale,
        );
    }
}
