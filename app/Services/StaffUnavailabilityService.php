<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Staff;
use App\Models\StaffUnavailability;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StaffUnavailabilityService
{
    public function __construct(private readonly UuidResolver $uuid_resolver)
    {
    }

    public function paginate(int $perPage = 15, ?int $staffId = null): LengthAwarePaginator
    {
        return StaffUnavailability::query()
            ->with('staff.user')
            ->when($staffId, fn ($query) => $query->where('staff_id', $staffId))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): StaffUnavailability
    {
        $data['staff_id'] = $this->uuid_resolver->resolve(Staff::class, $data['staff_uuid']);

        return StaffUnavailability::create($data);
    }

    public function show(StaffUnavailability $staffUnavailability): StaffUnavailability
    {
        return $staffUnavailability->load('staff.user');
    }

    public function update(StaffUnavailability $staffUnavailability, array $data): StaffUnavailability
    {
        if (isset($data['staff_uuid'])) {
            $data['staff_id'] = $this->uuid_resolver->resolve(Staff::class, $data['staff_uuid']);
        }

        $staffUnavailability->update($data);

        return $staffUnavailability->refresh();
    }

    public function destroy(StaffUnavailability $staffUnavailability): void
    {
        $staffUnavailability->delete();
    }
}
