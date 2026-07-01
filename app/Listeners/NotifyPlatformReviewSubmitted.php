<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PlatformReviewSubmitted;
use App\Notifications\Reviews\PlatformReviewSubmittedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyPlatformReviewSubmitted
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(PlatformReviewSubmitted $event): void
    {
        $platformReview = $event->platformReview;
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                PlatformReviewSubmittedNotification::forAdmin($platformReview, $admin->locale ?? $locale),
            );
        }

        $this->logService->markSent('platform.review.submitted', $platformReview->user_id, 'system');
    }
}
