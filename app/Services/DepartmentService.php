<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Facility;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DepartmentService
{
    public function __construct(private readonly UuidResolver $uuid_resolver) {
        //
    }
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
        if(isset($data['image'])){
            $data['image'] = $data['image']->store('departments',['disk' => 'public']);
        }
        $data['facility_id'] = $this->uuid_resolver->resolve(Facility::class,$data['facility_id']);

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
        $department_image = $department->image;
         if(isset($data['image'])){
            $data['image'] = $data['image']->store('departments',['disk' => 'public']);
        }
        $data['facility_id'] = $this->uuid_resolver->resolve(Facility::class,$data['facility_id']);

        $department->update($data);
        if($department_image && isset($data['image'])){
            Storage::disk('public')->delete($department_image);
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
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(is_active = 1) as active,
                        SUM(is_active = 0) as inactive
                    ")
                    ->first();

        return [
            'total' => (int) $stats->total,
            'active' => (int) $stats->active,
            'inactive' => (int) $stats->inactive,
        ];
    }
}
