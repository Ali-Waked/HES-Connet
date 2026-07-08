<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\StaffUnassigned;
use App\Notifications\Staff\StaffUnassignedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyStaffUnassigned
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(StaffUnassigned $event): void
    {
        $facilityStaff = $event->facilityStaff;
        $locale = app()->getLocale();

        $staffUser = $this->resolver->staffUser($facilityStaff);
        if ($staffUser) {
            $staffUser->notify(
                StaffUnassignedNotification::forStaff($facilityStaff, $staffUser->locale?->value ?? $locale),
            );
        }

        $facilityAdmins = $this->resolver->facilityAdmins($facilityStaff);
        foreach ($facilityAdmins as $admin) {
            $admin->notify(
                StaffUnassignedNotification::forFacilityAdmins($facilityStaff, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('staff.unassigned', $facilityStaff->staff?->user_id ?? 0, 'system');
    }
}
