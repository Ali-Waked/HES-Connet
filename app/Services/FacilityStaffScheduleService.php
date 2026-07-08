<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Facility;
use App\Models\StaffSchedule;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FacilityStaffScheduleService
{
    public function index(Facility $facility): Collection
    {
        return StaffSchedule::query()
            ->with('facilityStaff.staff')
            ->whereHas('facilityStaff', fn ($q) => $q->where('facility_id', $facility->id))
            ->latest()
            ->get();
    }

    public function show(
        Facility $facility,
        StaffSchedule $staffSchedule,
    ): StaffSchedule {
        return $this->ensureBelongsToFacility($facility, $staffSchedule)
            ->load('facilityStaff.staff');
    }

    public function store(
        Facility $facility,
        array $data,
    ): StaffSchedule {
        $facilityStaff = $facility->facilityStaff()
            ->whereHas('staff', fn ($q) => $q->where('uuid', $data['staff_uuid']))
            ->firstOrFail();

        StaffSchedule::where('facility_staff_id', $facilityStaff->id)
            ->whereIn('day_of_week', $data['days_of_week'])
            ->delete();

        $now = now();

        $rows = collect($data['days_of_week'])
            ->map(fn ($day) => [
                'facility_staff_id' => $facilityStaff->id,
                'day_of_week' => $day,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'slot_duration' => $data['slot_duration'],
                'is_active' => $data['is_active'] ?? true,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        StaffSchedule::insert($rows);

        return StaffSchedule::where('facility_staff_id', $facilityStaff->id)
            ->whereIn('day_of_week', $data['days_of_week'])
            ->first();
    }

    public function update(
        Facility $facility,
        StaffSchedule $staffSchedule,
        array $data,
    ): StaffSchedule {
        $staffSchedule = $this->ensureBelongsToFacility(
            $facility,
            $staffSchedule,
        );

        if (isset($data['staff_uuid'])) {
            $staffSchedule->facility_staff_id = $facility->facilityStaff()
                ->whereHas('staff', fn ($query) => $query->where('uuid', $data['staff_uuid']))
                ->value('id');

            unset($data['staff_uuid']);
        }

        $days = $data['days_of_week'] ?? null;
        unset($data['days_of_week']);

        $staffSchedule->fill($data)->save();

        if ($days !== null) {
            StaffSchedule::where('facility_staff_id', $staffSchedule->facility_staff_id)
                ->delete();

            $now = now();

            $rows = collect($days)
                ->map(fn ($day) => [
                    'facility_staff_id' => $staffSchedule->facility_staff_id,
                    'day_of_week' => $day,
                    'start_time' => $staffSchedule->start_time,
                    'end_time' => $staffSchedule->end_time,
                    'slot_duration' => $staffSchedule->slot_duration,
                    'is_active' => $staffSchedule->is_active,
                    'created_at' => $staffSchedule->created_at,
                    'updated_at' => $now,
                ])
                ->all();

            StaffSchedule::insert($rows);
        }

        return $staffSchedule->load('facilityStaff.staff');
    }

    public function destroy(
        Facility $facility,
        StaffSchedule $staffSchedule,
    ): void {
        $this->ensureBelongsToFacility(
            $facility,
            $staffSchedule,
        )->delete();
    }

    private function ensureBelongsToFacility(
        Facility $facility,
        StaffSchedule $staffSchedule,
    ): StaffSchedule {
        if ($staffSchedule->facilityStaff->facility_id !== $facility->id) {
            throw new NotFoundHttpException;
        }

        return $staffSchedule;
    }
}
