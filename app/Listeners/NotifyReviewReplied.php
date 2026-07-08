<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReviewReplied;
use App\Notifications\Reviews\ReviewRepliedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyReviewReplied
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(ReviewReplied $event): void
    {
        $reviewReply = $event->reviewReply;
        $locale = app()->getLocale();

        $patient = $reviewReply->review?->patient?->user;
        if ($patient) {
            $patient->notify(
                ReviewRepliedNotification::forPatient($reviewReply, $patient->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('review.replied', $reviewReply->review?->patient?->user_id ?? 0, 'system');
    }
}
