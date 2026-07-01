<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\JobPosted;
use App\Notifications\JobPosts\JobPostedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyJobPosted
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(JobPosted $event): void
    {
        $jobPost = $event->jobPost;
        $locale = app()->getLocale();

        // Notify admins
        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                JobPostedNotification::forAdmin($jobPost, $admin->locale ?? $locale),
            );
        }

        $this->logService->markSent('job.posted', $jobPost->user_id, 'system');
    }
}
