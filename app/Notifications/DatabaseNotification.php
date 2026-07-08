<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Data\NotificationData;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DatabaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly NotificationData $notificationData,
    ) {
        $this->onQueue('notifications');
    }

    public function via(User $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(User $notifiable): array
    {
        return $this->notificationData->toArray();
    }

    public function databaseType(): string
    {
        return $this->notificationData->type->value;
    }
}
