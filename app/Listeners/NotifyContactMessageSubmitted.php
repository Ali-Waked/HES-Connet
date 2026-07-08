<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ContactMessageSubmitted;
use App\Mail\ContactMessageConfirmation;
use App\Notifications\ContactMessageSubmittedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;
use Illuminate\Support\Facades\Mail;

class NotifyContactMessageSubmitted
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(ContactMessageSubmitted $event): void
    {
        $contactMessage = $event->contactMessage;
        $locale = app()->getLocale();

        Mail::to($contactMessage->email)
            ->send(new ContactMessageConfirmation($contactMessage));

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                ContactMessageSubmittedNotification::forAdmin($contactMessage, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('contact.submitted', 0, 'system');
    }
}
