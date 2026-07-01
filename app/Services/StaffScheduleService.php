<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FacilityStaff;
use App\Models\StaffSchedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StaffScheduleService
{
    public function __construct(private readonly UuidResolver $uuid_resolver) {}

    public function paginate(int $perPage = 15, ?int $facilityStaffId = null): LengthAwarePaginator
    {
        return StaffSchedule::query()
            ->with('facilityStaff.staff.user', 'facilityStaff.facility')
            ->when($facilityStaffId, fn ($query) => $query->where('facility_staff_id', $facilityStaffId))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): StaffSchedule
    {
        $data['facility_staff_id'] = $this->uuid_resolver->resolve(FacilityStaff::class, $data['facility_staff_uuid']);

        return StaffSchedule::create($data);
    }

    public function show(StaffSchedule $staffSchedule): StaffSchedule
    {
        return $staffSchedule->load('facilityStaff.staff.user', 'facilityStaff.facility');
    }

    public function update(StaffSchedule $staffSchedule, array $data): StaffSchedule
    {
        if (isset($data['facility_staff_uuid'])) {
            $data['facility_staff_id'] = $this->uuid_resolver->resolve(FacilityStaff::class, $data['facility_staff_uuid']);
        }

        $staffSchedule->update($data);

        return $staffSchedule->refresh();
    }

    public function destroy(StaffSchedule $staffSchedule): void
    {
        $staffSchedule->delete();
    }
}
