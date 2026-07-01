<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StaffUnavailabilityStatus;
use App\Models\Facility;
use App\Models\StaffUnavailability;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FacilityStaffUnavailabilityService
{
    public function index(
        Facility $facility,
        Request $request,
    ) {
        return StaffUnavailability::query()
            ->where('facility_id', $facility->id)
            ->with('staff')
            ->when(
                $request->filled('status'),
                fn (Builder $query) => $query->where(
                    'status',
                    $request->string('status')
                )
            )
            ->latest()
            ->paginate(
                $request->integer('per_page', 15)
            );
    }

    public function show(
        Facility $facility,
        StaffUnavailability $unavailability,
    ): StaffUnavailability {
        return $this->ensureFacility(
            $facility,
            $unavailability
        )->load('staff');
    }

    public function approve(
        Facility $facility,
        StaffUnavailability $unavailability,
    ): StaffUnavailability {
        $unavailability = $this->ensureFacility(
            $facility,
            $unavailability
        );

        $unavailability->update([
            'status' => StaffUnavailabilityStatus::APPROVED,
        ]);

        return $unavailability->fresh('staff');
    }

    public function reject(
        Facility $facility,
        StaffUnavailability $unavailability,
    ): StaffUnavailability {
        $unavailability = $this->ensureFacility(
            $facility,
            $unavailability
        );

        $unavailability->update([
            'status' => StaffUnavailabilityStatus::REJECTED,
        ]);

        return $unavailability->fresh('staff');
    }

    private function ensureFacility(
        Facility $facility,
        StaffUnavailability $unavailability,
    ): StaffUnavailability {
        if ($unavailability->facility_id !== $facility->id) {
            throw new NotFoundHttpException;
        }

        return $unavailability;
    }
}
