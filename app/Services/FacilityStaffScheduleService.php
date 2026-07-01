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
            ->with('staff')
            ->where('facility_id', $facility->id)
            ->latest()
            ->get();
    }

    public function show(
        Facility $facility,
        StaffSchedule $staffSchedule,
    ): StaffSchedule {
        return $this->ensureBelongsToFacility($facility, $staffSchedule)
            ->load('staff');
    }

    public function store(
        Facility $facility,
        array $data,
    ): StaffSchedule {
        $staff = $facility->staff()
            ->where('uuid', $data['staff_uuid'])
            ->firstOrFail();

        return StaffSchedule::create([
            'facility_id' => $facility->id,
            'staff_id' => $staff->id,
            'days_of_week' => $data['days_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'slot_duration' => $data['slot_duration'],
            'is_active' => $data['is_active'] ?? true,
        ]);
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
            $staff = $facility->staff()
                ->where('uuid', $data['staff_uuid'])
                ->firstOrFail();

            $data['staff_id'] = $staff->id;
        }

        unset($data['staff_uuid']);

        $staffSchedule->update($data);

        return $staffSchedule->fresh('staff');
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
        if ($staffSchedule->facility_id !== $facility->id) {
            throw new NotFoundHttpException;
        }

        return $staffSchedule;
    }
}
