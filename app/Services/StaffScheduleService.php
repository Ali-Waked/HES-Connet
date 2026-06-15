<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Facility;
use App\Models\Staff;
use App\Models\StaffSchedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StaffScheduleService
{
    public function __construct(private readonly UuidResolver $uuid_resolver)
    {
    }

    public function paginate(int $perPage = 15, ?int $staffId = null): LengthAwarePaginator
    {
        return StaffSchedule::query()
            ->with('staff.user', 'facility')
            ->when($staffId, fn ($query) => $query->where('staff_id', $staffId))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): StaffSchedule
    {
        $data['staff_id'] = $this->uuid_resolver->resolve(Staff::class, $data['staff_uuid']);
        $data['facility_id'] = $this->uuid_resolver->resolve(Facility::class, $data['facility_uuid']);

        return StaffSchedule::create($data);
    }

    public function show(StaffSchedule $staffSchedule): StaffSchedule
    {
        return $staffSchedule->load('staff.user', 'facility');
    }

    public function update(StaffSchedule $staffSchedule, array $data): StaffSchedule
    {
        if (isset($data['staff_uuid'])) {
            $data['staff_id'] = $this->uuid_resolver->resolve(Staff::class, $data['staff_uuid']);
        }
        if (isset($data['facility_uuid'])) {
            $data['facility_id'] = $this->uuid_resolver->resolve(Facility::class, $data['facility_uuid']);
        }

        $staffSchedule->update($data);

        return $staffSchedule->refresh();
    }

    public function destroy(StaffSchedule $staffSchedule): void
    {
        $staffSchedule->delete();
    }
}
