<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Facility;
use App\Models\FacilityStaff;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class DepartmentService
{
    public function __construct(private readonly UuidResolver $uuid_resolver)
    {
        //
    }

    public function paginate(int $perPage = 15, ?string $search = null, ?int $facilityId = null): LengthAwarePaginator
    {

        return Department::query()
            ->with([
                'head',
                'facilityStaff.facility',
            ])
            ->when(
                $search,
                fn ($query) => $query->where(function ($q) use ($search) {
                    $q->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%");
                })
            )
            ->when(
                $facilityId,
                fn ($query) => $query->where(
                    'facility_id',
                    $facilityId
                )
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Department
    {
        if (isset($data['image'])) {
            $data['image'] = $data['image']->store('departments', ['disk' => 'public']);
        }
        $facility = Facility::whereUuid($data['facility_id'])->firstOrFail();
        $head = FacilityStaff::query()->where('facility_id', $facility->id)
            ->where('uuid', $data['head_facility_staff_id'])
            ->whereDoesntHave('headedDepartment')
            ->firstOrFail();

        $data['head_facility_staff_id'] = $head->id;

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
        $oldImage = $department->image;

        $data['facility_id'] = $this->uuid_resolver->resolve(
            Facility::class,
            $data['facility_id']
        );

        $newImage = null;

        if (! empty($data['image'])) {
            $newImage = $data['image']->store('departments', [
                'disk' => 'public',
            ]);

            $data['image'] = $newImage;
        } else {
            unset($data['image']);
        }

        $department->update($data);

        if ($newImage && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return $department->refresh();
    }

    public function destroy(
        Department $department
    ): void {

        $department->delete();
    }

    public function getStats(): array
    {
        $stats = Department::query()
            ->selectRaw('
                        COUNT(*) as total,
                        SUM(is_active = 1) as active,
                        SUM(is_active = 0) as inactive
                    ')
            ->first();

        return [
            'total' => (int) $stats->total,
            'active' => (int) $stats->active,
            'inactive' => (int) $stats->inactive,
        ];
    }
}
