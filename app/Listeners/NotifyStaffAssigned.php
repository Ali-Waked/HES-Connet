<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\StaffAssigned;
use App\Notifications\Staff\StaffAssignedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyStaffAssigned
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(StaffAssigned $event): void
    {
        $facilityStaff = $event->facilityStaff;
        $locale = app()->getLocale();

        // Notify the staff member
        $staffUser = $this->resolver->staffUser($facilityStaff);
        if ($staffUser) {
            $staffUser->notify(
                StaffAssignedNotification::forStaff($facilityStaff, $staffUser->locale?->value ?? $locale),
            );
        }

        // Notify facility admins
        $facilityAdmins = $this->resolver->facilityAdmins($facilityStaff);
        foreach ($facilityAdmins as $admin) {
            $admin->notify(
                StaffAssignedNotification::forFacilityAdmins($facilityStaff, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('staff.assigned', $facilityStaff->staff?->user_id ?? 0, 'system');
    }
}
