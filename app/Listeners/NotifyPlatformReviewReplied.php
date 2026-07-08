<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PlatformReviewReplied;
use App\Notifications\Reviews\PlatformReviewRepliedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyPlatformReviewReplied
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(PlatformReviewReplied $event): void
    {
        $platformReview = $event->platformReview;
        $locale = app()->getLocale();

        $owner = $platformReview->user;
        if ($owner) {
            $owner->notify(
                PlatformReviewRepliedNotification::forOwner($platformReview, $owner->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('platform.review.replied', $platformReview->user_id, 'system');
    }
}
