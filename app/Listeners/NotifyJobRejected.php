<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\JobRejected;
use App\Notifications\JobPosts\JobRejectedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyJobRejected
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(JobRejected $event): void
    {
        $jobPost = $event->jobPost;
        $locale = app()->getLocale();

        $creator = $this->resolver->jobPostCreator($jobPost);
        if ($creator) {
            $creator->notify(
                JobRejectedNotification::forOwner($jobPost, $creator->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('job.rejected', $jobPost->user_id, 'system');
    }
}
