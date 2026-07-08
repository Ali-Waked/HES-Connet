<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\JobApproved;
use App\Notifications\JobPosts\JobApprovedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyJobApproved
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(JobApproved $event): void
    {
        $jobPost = $event->jobPost;
        $locale = app()->getLocale();

        $creator = $this->resolver->jobPostCreator($jobPost);
        if ($creator) {
            $creator->notify(
                JobApprovedNotification::forOwner($jobPost, $creator->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('job.approved', $jobPost->user_id, 'system');
    }
}
