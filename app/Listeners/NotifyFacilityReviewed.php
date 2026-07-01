<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FacilityReviewed;
use App\Models\User;
use App\Notifications\Reviews\FacilityReviewedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyFacilityReviewed
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(FacilityReviewed $event): void
    {
        $facilityReview = $event->facilityReview;
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                FacilityReviewedNotification::forAdmin($facilityReview, $admin->locale ?? $locale),
            );
        }

        $facility = $facilityReview->facility;
        $facilityAdmins = User::whereHas('staff.facilityStaff', fn ($q) => $q
            ->where('facility_id', $facility->id)
            ->whereNull('ended_at')
            ->whereHas('role', fn ($r) => $r->where('slug', 'facility_admin'))
        )->get();

        foreach ($facilityAdmins as $admin) {
            $admin->notify(
                FacilityReviewedNotification::forFacilityAdmin($facilityReview, $admin->locale ?? $locale),
            );
        }

        $this->logService->markSent('facility.reviewed', $facilityReview->patient?->user_id ?? 0, 'system');
    }
}
