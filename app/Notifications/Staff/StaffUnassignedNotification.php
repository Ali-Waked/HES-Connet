<?php

declare(strict_types=1);

namespace App\Notifications\Staff;

use App\Models\FacilityStaff;
use App\Notifications\BaseNotification;

class StaffUnassignedNotification extends BaseNotification
{
    public static function forStaff(FacilityStaff $facilityStaff, ?string $locale = null): static
    {
        return new static(
            event: 'staff.unassigned',
            role: 'staff',
            data: [
                'facility' => $facilityStaff->facility?->name ?? 'A facility',
                'position' => $facilityStaff->role?->name ?? 'Staff',
            ],
            locale: $locale,
        );
    }

    public static function forFacilityAdmins(FacilityStaff $facilityStaff, ?string $locale = null): static
    {
        return new static(
            event: 'staff.unassigned',
            role: 'facility_admins',
            data: [
                'staff' => $facilityStaff->staff?->user?->name ?? 'A staff member',
                'facility' => $facilityStaff->facility?->name ?? 'A facility',
                'position' => $facilityStaff->role?->name ?? 'Staff',
            ],
            locale: $locale,
        );
    }
}
