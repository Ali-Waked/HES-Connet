<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DoctorReviewed;
use App\Notifications\Reviews\DoctorReviewedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyDoctorReviewed
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(DoctorReviewed $event): void
    {
        $review = $event->review;
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                DoctorReviewedNotification::forAdmin($review, $admin->locale ?? $locale),
            );
        }

        $doctor = $review->appointment?->facilityStaff?->staff?->user;
        if ($doctor) {
            $doctor->notify(
                DoctorReviewedNotification::forDoctor($review, $doctor->locale ?? $locale),
            );
        }

        $this->logService->markSent('doctor.reviewed', $review->patient?->user_id ?? 0, 'system');
    }
}
