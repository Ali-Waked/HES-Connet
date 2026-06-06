<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DepartmentService
{
    public function paginate(int $perPage = 15, ?string $search = null, ?int $facilityId = null): LengthAwarePaginator
    {

        return Department::query()
            ->with([
                'facility',
                'head',
            ])
            ->when(
                $search,
                fn ($query) =>
                $query->where(
                    'name',
                    'like',
                    "%{$search}%"
                )
            )
            ->when(
                $facilityId,
                fn ($query) =>
                $query->where(
                    'facility_id',
                    $facilityId
                )
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Department
    {
        return Department::create($data);
    }

    public function show(Department $department): Department
    {
        return $department->load([
                'facility',
                'head',
        ]);
    }

    public function update(Department $department, array $data): Department 
    {

        $department->update($data);

        return $department->refresh();
    }

    public function destroy(
        Department $department
    ): void {

        $department->delete();
    }
}