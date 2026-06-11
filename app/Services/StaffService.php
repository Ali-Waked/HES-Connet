<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Staff;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StaffService
{
    public function paginate(int $perPage = 15, ?string $search = null, ?int $facilityId = null): LengthAwarePaginator
    {
        return Staff::query()
            ->with(['user', 'facilityStaff.facility'])
            ->when($search, fn ($query) => $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")))
            ->when($facilityId, fn ($query) => $query->whereHas('facilityStaff', fn ($q) => $q->where('facility_id', $facilityId)))
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(array $data): Staff
    {
        return Staff::create($data);
    }

    public function show(Staff $staff): Staff
    {
        return $staff->load([
            'user',
            'facilityStaff.facility',
            'departmentsAsHead',
            'doctorSchedules',
            'symptoms',
        ]);
    }

    public function update(Staff $staff, array $data): Staff
    {
        $staff->update($data);

        return $staff->refresh();
    }

    public function destroy(Staff $staff): void
    {
        $staff->delete();
    }
}
