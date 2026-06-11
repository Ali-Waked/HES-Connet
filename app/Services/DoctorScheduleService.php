<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DoctorSchedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DoctorScheduleService
{
    public function paginate(int $perPage = 15, ?int $staffId = null): LengthAwarePaginator
    {
        return DoctorSchedule::query()
            ->with('staff.user')
            ->when($staffId, fn ($query) => $query->where('staff_id', $staffId))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): DoctorSchedule
    {
        return DoctorSchedule::create($data);
    }

    public function show(DoctorSchedule $doctorSchedule): DoctorSchedule
    {
        return $doctorSchedule->load('staff.user');
    }

    public function update(DoctorSchedule $doctorSchedule, array $data): DoctorSchedule
    {
        $doctorSchedule->update($data);

        return $doctorSchedule->refresh();
    }

    public function destroy(DoctorSchedule $doctorSchedule): void
    {
        $doctorSchedule->delete();
    }
}
