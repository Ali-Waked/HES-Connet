<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DonationCompleted;
use App\Notifications\Donations\DonationCompletedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyDonationCompleted
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(DonationCompleted $event): void
    {
        $donation = $event->donation;
        $locale = app()->getLocale();

        $donor = $donation->donor;
        if ($donor) {
            $donor->notify(
                DonationCompletedNotification::forDonor($donation, $donor->locale?->value ?? $locale),
            );
        }

        $patient = $this->resolver->storyOwner($donation->story);
        if ($patient) {
            $patient->notify(
                DonationCompletedNotification::forPatient($donation, $patient->locale?->value ?? $locale),
            );
        }

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                DonationCompletedNotification::forAdmin($donation, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('donation.completed', (int) ($donation->donor_id ?? 0), 'system');
    }
}
