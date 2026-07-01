<?php

declare(strict_types=1);

namespace App\Notifications\Users;

use App\Models\User;
use App\Notifications\BaseNotification;

class UserLoggedInNotification extends BaseNotification
{
    public static function forOwner(User $user, ?string $locale = null): static
    {
        return new static(
            event: 'user.logged_in',
            role: 'owner',
            data: ['name' => $user->name, 'email' => $user->email],
            locale: $locale,
        );
    }
}
