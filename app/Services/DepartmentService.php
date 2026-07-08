<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Department;
use App\Models\Facility;
use App\Models\FacilityStaff;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DepartmentService
{
    public function paginate(int $perPage = 15, ?string $search = null, ?string $facilityUuid = null): LengthAwarePaginator
    {
        return Department::query()
            ->with('head.staff.user', 'facility')
            ->when($facilityUuid, fn ($q, $uuid) => $q->whereHas('facility', fn ($q) => $q->where('uuid', $uuid)))
            ->when($search, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ar', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate($perPage);
    }

    public function getStats(): array
    {
        return [
            'total_departments' => Department::count(),
            'active_departments' => Department::where('is_active', true)->count(),
            'inactive_departments' => Department::where('is_active', false)->count(),
        ];
    }

    public function dashboardCreate(array $data): Department
    {
        if (isset($data['head_facility_staff_id'])) {
            $head = FacilityStaff::where('uuid', $data['head_facility_staff_id'])->firstOrFail();
            $data['head_facility_staff_id'] = $head->id;
        }

        return Department::create($data);
    }

    public function dashboardShow(Department $department): Department
    {
        return $department->load('head.staff.user', 'facility');
    }

    public function dashboardUpdate(Department $department, array $data): Department
    {
        if (isset($data['head_facility_staff_id'])) {
            $head = FacilityStaff::where('uuid', $data['head_facility_staff_id'])->firstOrFail();
            $data['head_facility_staff_id'] = $head->id;
        }

        $department->update($data);

        return $department->fresh('head.staff.user', 'facility');
    }

    public function dashboardDestroy(Department $department): void
    {
        $department->delete();
    }

    public function index(Facility $facility): Collection
    {
        return Department::query()
            ->where('facility_id', $facility->id)
            ->with('head.staff.user')
            ->latest()
            ->get();
    }

    public function show(Facility $facility, Department $department): Department
    {
        return $this->ensureBelongsToFacility($facility, $department)
            ->load('head.staff.user');
    }

    public function store(Facility $facility, array $data): Department
    {
        $data['facility_id'] = $facility->id;

        if (isset($data['head_facility_staff_uuid'])) {
            $head = $facility->facilityStaff()
                ->where('uuid', $data['head_facility_staff_uuid'])
                ->firstOrFail();

            $data['head_facility_staff_id'] = $head->id;
            unset($data['head_facility_staff_uuid']);
        }

        return Department::create($data);
    }

    public function update(
        Facility $facility,
        Department $department,
        array $data,
    ): Department {
        $department = $this->ensureBelongsToFacility($facility, $department);

        if (isset($data['head_facility_staff_uuid'])) {
            $head = $facility->facilityStaff()
                ->where('uuid', $data['head_facility_staff_uuid'])
                ->firstOrFail();

            $data['head_facility_staff_id'] = $head->id;
            unset($data['head_facility_staff_uuid']);
        }

        $department->update($data);

        return $department->fresh('head.staff.user');
    }

    public function destroy(Facility $facility, Department $department): void
    {
        $this->ensureBelongsToFacility($facility, $department)->delete();
    }

    private function ensureBelongsToFacility(
        Facility $facility,
        Department $department,
    ): Department {
        if ($department->facility_id !== $facility->id) {
            throw new NotFoundHttpException;
        }

        return $department;
    }
}
