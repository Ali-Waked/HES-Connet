<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Models\User;
use Illuminate\Notifications\Notification;
use RuntimeException;
use Twilio\Rest\Client;

class TwilioSmsChannel
{
    public function send(User $notifiable, Notification $notification): void
    {
        $phone = $notifiable->profile?->phone;

        if (! $phone) {
            return;
        }

        $message = $notification->toTwilioSms($notifiable);

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (! $sid || ! $token || ! $from) {
            throw new RuntimeException('Twilio is not configured.');
        }

        $client = new Client($sid, $token);

        $client->messages->create($phone, [
            'from' => $from,
            'body' => $message,
        ]);
    }
}
