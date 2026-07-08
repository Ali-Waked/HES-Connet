<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\StoryRejected;
use App\Notifications\Stories\StoryRejectedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyStoryRejected
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(StoryRejected $event): void
    {
        $story = $event->story;
        $locale = app()->getLocale();

        $patient = $this->resolver->storyOwner($story);
        if ($patient) {
            $patient->notify(
                StoryRejectedNotification::forPatient($story, $patient->locale?->value ?? $locale),
            );
        }

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                StoryRejectedNotification::forAdmin($story, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('story.rejected', $story->patient?->user_id ?? 0, 'system');
    }
}
