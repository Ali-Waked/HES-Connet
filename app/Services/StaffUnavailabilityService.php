<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FacilityStaff;
use App\Models\StaffUnavailability;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StaffUnavailabilityService
{
    public function __construct(private readonly UuidResolver $uuid_resolver)
    {
    }

    public function paginate(int $perPage = 15, ?int $facilityStaffId = null): LengthAwarePaginator
    {
        return StaffUnavailability::query()
            ->with('facilityStaff.staff.user')
            ->when($facilityStaffId, fn ($query) => $query->where('facility_staff_id', $facilityStaffId))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): StaffUnavailability
    {
        // $data['facility_staff_id'] = $this->uuid_resolver->resolve(FacilityStaff::class, $data['facility_staff_uuid']);

        return StaffUnavailability::create($data);
    }

    public function show(StaffUnavailability $staffUnavailability): StaffUnavailability
    {
        return $staffUnavailability->load('facilityStaff.staff.user');
    }

    public function update(StaffUnavailability $staffUnavailability, array $data): StaffUnavailability
    {
        if (isset($data['facility_staff_uuid'])) {
            $data['facility_staff_id'] = $this->uuid_resolver->resolve(FacilityStaff::class, $data['facility_staff_uuid']);
        }

        $staffUnavailability->update($data);

        return $staffUnavailability->refresh();
    }

    public function destroy(StaffUnavailability $staffUnavailability): void
    {
        $staffUnavailability->delete();
    }
}
