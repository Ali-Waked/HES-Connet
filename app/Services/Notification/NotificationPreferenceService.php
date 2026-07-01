<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Models\User;

class NotificationPreferenceService
{
    private const CHANNEL_MAP = [
        'mail' => 'email_notifications',
        'broadcast' => 'push_notifications',
        'twilio' => 'sms_notifications',
    ];

    public function canReceive(User $user, string $channel): bool
    {
        $column = self::CHANNEL_MAP[$channel] ?? null;

        if ($column === null) {
            return true;
        }

        return (bool) $user->{$column};
    }

    public function filterChannels(User $user, array $channels): array
    {
        return array_values(array_filter(
            $channels,
            fn (string $channel) => $this->canReceive($user, $channel),
        ));
    }
}
