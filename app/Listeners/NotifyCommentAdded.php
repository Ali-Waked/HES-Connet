<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CommentAdded;
use App\Notifications\Comments\CommentAddedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyCommentAdded
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(CommentAdded $event): void
    {
        $comment = $event->comment;
        $locale = app()->getLocale();

        // Notify article owner (if different from commenter)
        $owner = $this->resolver->commentContentOwner($comment);
        if ($owner && $owner->id !== $comment->user_id) {
            $owner->notify(
                CommentAddedNotification::forOwner($comment, $owner->locale?->value ?? $locale),
            );
        }

        // Notify admins
        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                CommentAddedNotification::forAdmin($comment, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('comment.added', $comment->user_id, 'system');
    }
}
