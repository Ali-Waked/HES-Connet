<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DoctorUnavailable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DoctorUnavailableService
{
    public function paginate(int $perPage = 15, ?int $staffId = null): LengthAwarePaginator
    {
        return DoctorUnavailable::query()
            ->with('staff.user')
            ->when($staffId, fn ($query) => $query->where('staff_id', $staffId))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): DoctorUnavailable
    {
        return DoctorUnavailable::create($data);
    }

    public function show(DoctorUnavailable $doctorUnavailable): DoctorUnavailable
    {
        return $doctorUnavailable->load('staff.user');
    }

    public function update(DoctorUnavailable $doctorUnavailable, array $data): DoctorUnavailable
    {
        $doctorUnavailable->update($data);

        return $doctorUnavailable->refresh();
    }

    public function destroy(DoctorUnavailable $doctorUnavailable): void
    {
        $doctorUnavailable->delete();
    }
}
