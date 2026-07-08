<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Notifications\DatabaseNotification;

class NotificationPolicy
{
    use HandlesAuthorization;

    public function view(User $user, DatabaseNotification $notification): bool
    {
        return $user->id === $notification->notifiable_id
            && $notification->notifiable_type === get_class($user);
    }

    public function markAsRead(User $user, DatabaseNotification $notification): bool
    {
        return $this->view($user, $notification);
    }

    public function delete(User $user, DatabaseNotification $notification): bool
    {
        return $this->view($user, $notification);
    }
}
