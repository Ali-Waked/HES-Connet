<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\StoryApproved;
use App\Notifications\Stories\StoryApprovedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyStoryApproved
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(StoryApproved $event): void
    {
        $story = $event->story;
        $locale = app()->getLocale();

        // Notify patient
        $patient = $this->resolver->storyOwner($story);
        if ($patient) {
            $patient->notify(
                StoryApprovedNotification::forPatient($story, $patient->locale?->value ?? $locale),
            );
        }

        // Notify admins
        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                StoryApprovedNotification::forAdmin($story, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('story.approved', $story->patient?->user_id ?? 0, 'system');
    }
}
