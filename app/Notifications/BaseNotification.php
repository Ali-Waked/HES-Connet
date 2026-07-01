<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use App\Services\Notification\NotificationMatrix;
use App\Services\Notification\NotificationPreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $locale;

    public function __construct(
        public readonly string $event,
        public readonly string $role,
        public readonly array $data = [],
        ?string $locale = null,
    ) {
        $this->locale = $locale ?? app()->getLocale();
        $this->onQueue('notifications');
    }

    public function via(User $notifiable): array
    {
        $matrix = app(NotificationMatrix::class);
        $prefs = app(NotificationPreferenceService::class);

        $channels = $matrix->channels($this->event, $this->role);

        return $prefs->filterChannels($notifiable, $channels);
    }

    public function toDatabase(User $notifiable): array
    {
        return [
            'title' => __("notifications.{$this->event}.title", [], $this->locale),
            'body' => __("notifications.{$this->event}.body", $this->data, $this->locale),
            'type' => $this->event,
            'data' => $this->data,
        ];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $lang = $this->locale;
        $dir = $lang === 'ar' ? 'rtl' : 'ltr';

        return (new MailMessage)
            ->subject(__("notifications.{$this->event}.title", [], $lang))
            ->view('emails.notification', [
                'lang' => $lang,
                'dir' => $dir,
                'name' => $notifiable->name,
                'title' => __("notifications.{$this->event}.title", [], $lang),
                'body' => __("notifications.{$this->event}.body", $this->data, $lang),
                'actionText' => $this->data['action_text'] ?? null,
                'actionUrl' => $this->data['action_url'] ?? null,
            ]);
    }

    public function toBroadcast(User $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => __("notifications.{$this->event}.title", [], $this->locale),
            'body' => __("notifications.{$this->event}.body", $this->data, $this->locale),
            'type' => $this->event,
            'data' => $this->data,
        ]);
    }

    public function toTwilioSms(User $notifiable): string
    {
        return __("notifications.{$this->event}.sms", $this->data, $this->locale)
            ?: __("notifications.{$this->event}.body", $this->data, $this->locale);
    }

    public function broadcastType(): string
    {
        return "notification.{$this->event}";
    }

    public function databaseType(): string
    {
        return $this->event;
    }

    public function viaQueues(): array
    {
        return [
            'mail' => 'emails',
            'broadcast' => 'broadcast',
            'twilio' => 'sms',
        ];
    }
}
