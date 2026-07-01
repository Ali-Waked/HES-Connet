<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DonationMade;
use App\Notifications\Donations\DonationMadeNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyDonationMade
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(DonationMade $event): void
    {
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                DonationMadeNotification::forEvent(
                    donorName: $event->donorName,
                    amount: $event->amount,
                    campaign: $event->campaign,
                    locale: $admin->locale ?? $locale,
                ),
            );
        }

        $this->logService->markSent('donation.made', 0, 'system');
    }
}
