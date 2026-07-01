<?php

declare(strict_types=1);

namespace App\Notifications\Users;

use App\Models\User;
use App\Notifications\BaseNotification;

class UserRegisteredNotification extends BaseNotification
{
    public static function forOwner(User $user, ?string $locale = null): static
    {
        return new static(
            event: 'user.registered',
            role: 'owner',
            data: ['name' => $user->name, 'email' => $user->email],
            locale: $locale,
        );
    }

    public static function forAdmin(User $user, ?string $locale = null): static
    {
        return new static(
            event: 'user.registered',
            role: 'admin',
            data: ['name' => $user->name, 'email' => $user->email],
            locale: $locale,
        );
    }
}
