<?php

declare(strict_types=1);

namespace App\Services\Notification;

class NotificationMatrix
{
    public function channels(string $event, string $role): array
    {
        return config('notification-matrix')[$event][$role] ?? [];
    }

    public function hasChannel(string $event, string $role, string $channel): bool
    {
        return in_array($channel, $this->channels($event, $role), true);
    }

    public function roles(string $event): array
    {
        return array_keys(config('notification-matrix')[$event] ?? []);
    }
}
