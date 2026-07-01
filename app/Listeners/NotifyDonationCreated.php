<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DonationCreated;
use App\Notifications\Donations\DonationCreatedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyDonationCreated
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(DonationCreated $event): void
    {
        $donation = $event->donation;
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                DonationCreatedNotification::forAdmin($donation, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('donation.created', $donation->donor_id ?? 0, 'system');
    }
}
