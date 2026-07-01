<?php

declare(strict_types=1);

namespace App\Notifications\Staff;

use App\Models\FacilityStaff;
use App\Notifications\BaseNotification;

class StaffAssignedNotification extends BaseNotification
{
    public static function forStaff(FacilityStaff $facilityStaff, ?string $locale = null): static
    {
        return new static(
            event: 'staff.assigned',
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
            event: 'staff.assigned',
            role: 'facility_admins',
            data: [
                'facility' => $facilityStaff->facility?->name ?? 'A facility',
                'position' => $facilityStaff->role?->name ?? 'Staff',
            ],
            locale: $locale,
        );
    }
}
