<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class Notification extends BaseNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $event,
        public readonly array $data,
        public readonly string $locale,
        public readonly array $channels,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => __("notifications.{$this->event}.title", [], $this->locale),
            'body' => __("notifications.{$this->event}.body", $this->data, $this->locale),
            'type' => $this->event,
            'data' => $this->data,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = __("notifications.{$this->event}.title", [], $this->locale);
        $body = __("notifications.{$this->event}.body", $this->data, $this->locale);

        $mailMessage = (new MailMessage)
            ->subject($title)
            ->view('emails.notification', [
                'title' => $title,
                'body' => $body,
                'name' => $notifiable->name,
                'lang' => $this->locale,
                'dir' => $this->locale === 'ar' ? 'rtl' : 'ltr',
                'actionText' => $this->data['action_text'] ?? null,
                'actionUrl' => $this->data['action_url'] ?? null,
            ]);

        return $mailMessage;
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'title' => __("notifications.{$this->event}.title", [], $this->locale),
            'body' => __("notifications.{$this->event}.body", $this->data, $this->locale),
            'type' => $this->event,
            'data' => $this->data,
        ];
    }

    public function toTwilioSms(object $notifiable): string
    {
        return __("notifications.{$this->event}.sms", $this->data, $this->locale)
            ?: __("notifications.{$this->event}.body", $this->data, $this->locale);
    }

    public function broadcastType(): string
    {
        return "notification.{$this->event}";
    }
}
