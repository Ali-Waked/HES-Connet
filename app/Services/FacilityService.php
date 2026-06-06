<?php

namespace App\Services;

use App\Models\Facility;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FacilityService
{
    public function paginate(int $perPage = 15, ?string $search = null, ?string $type = null): LengthAwarePaginator {

        return Facility::query()->with(['organization','parent',])
            ->when(
                $search,
                fn ($query) => $query->where(
                    'name',
                    'like',
                    "%{$search}%"
                )
            )
            ->when(
                $type,
                fn ($query) => $query->where(
                    'facility_type',
                    $type
                )
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Facility
    {
        return Facility::create($data);
    }

    public function show(Facility $facility): Facility
    {
        return $facility->load([
                'organization',
                'parent',
                'children',
                'facilityImages',
                'facilityDocuments',
            ]);
    }

    public function update(
        Facility $facility,
        array $data
    ): Facility {

        $facility->update($data);

        return $facility->refresh();
    }

    public function destroy(Facility $facility): void
    {
        $facility->delete();
    }
}