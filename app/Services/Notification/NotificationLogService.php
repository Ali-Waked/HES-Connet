<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Models\NotificationLog;

class NotificationLogService
{
    public function log(
        string $type,
        int $userId,
        string $channel,
        string $status,
        ?string $error = null,
    ): NotificationLog {
        return NotificationLog::create([
            'type' => $type,
            'user_id' => $userId,
            'channel' => $channel,
            'status' => $status,
            'payload' => $error ? ['error' => $error] : null,
        ]);
    }

    public function markSent(string $type, int $userId, string $channel): NotificationLog
    {
        return $this->log($type, $userId, $channel, 'sent');
    }

    public function markFailed(string $type, int $userId, string $channel, string $error): NotificationLog
    {
        return $this->log($type, $userId, $channel, 'failed', $error);
    }
}
