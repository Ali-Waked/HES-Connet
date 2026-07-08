<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\StoryCreated;
use App\Notifications\Stories\StoryCreatedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyStoryCreated
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(StoryCreated $event): void
    {
        $story = $event->story;
        $locale = app()->getLocale();

        // Notify patient
        $patient = $this->resolver->storyOwner($story);
        if ($patient) {
            $patient->notify(
                StoryCreatedNotification::forPatient($story, $patient->locale?->value ?? $locale),
            );
        }

        // Notify admins
        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                StoryCreatedNotification::forAdmin($story, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('story.created', $story->patient?->user_id ?? 0, 'system');
    }
}
