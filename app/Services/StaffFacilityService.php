<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FacilityStatus;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Collection;

class StaffFacilityService
{
    public function getFacilities(Staff $staff): Collection
    {
        return $staff->facilities()
            ->wherePivotNull('ended_at')
            ->where('status', FacilityStatus::ACTIVE)
            ->with('city')
            ->get();
    }
}
