<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\Medicine;
use App\Models\PharmacyMedicine;
use App\Models\Staff;
use Illuminate\Http\Request;

class FacilityPortalService
{
    public function __construct(private readonly UuidResolver $uuid_resolver) {}

    public function resolveFacility(Request $request): Facility
    {
        $facilityStaff = $request->user()->getActiveFacilityStaff();

        abort_unless($facilityStaff, 403, __('You do not have an active facility workspace.'));

        return $facilityStaff->facility;
    }

    public function resolveFacilityStaff(Request $request): FacilityStaff
    {
        $facilityStaff = $request->user()->getActiveFacilityStaff();

        abort_unless($facilityStaff, 403, __('You do not have an active facility workspace.'));

        return $facilityStaff;
    }

    public function paginate(Staff $staff, Facility $facility, int $perPage = 15, ?string $search = null)
    {
        $this->ensureFacilityAccess($staff, $facility);

        return PharmacyMedicine::query()
            ->where('facility_id', $facility->id)

            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('uuid', 'like', "%{$search}%")
                        ->orWhereHas('medicine', function ($mq) use ($search) {
                            $mq->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->with(['medicine:id,uuid,name,image_url'])
            ->latest()
            ->paginate($perPage);
    }

    public function show(
        Staff $staff,
        Facility $facility,
        PharmacyMedicine $medicine
    ): PharmacyMedicine {
        $this->ensureFacilityMedicineAccess(
            $staff,
            $facility,
            $medicine
        );

        return $medicine->load('medicine');
    }

    public function store(
        Staff $staff,
        Facility $facility,
        array $data
    ): PharmacyMedicine {
        $this->ensureFacilityAccess($staff, $facility);

        $medicine = Medicine::whereUuid(
            $data['medicine_uuid']
        )->firstOrFail();

        $pharmacyMedicine = PharmacyMedicine::create([
            'facility_id' => $facility->id,
            'medicine_id' => $medicine->id,
            'stock' => $data['stock'],
            'price' => $data['price'] ?? null,
            'is_available' => $data['is_available'] ?? true,
        ]);

        return $pharmacyMedicine->load('medicine');
    }

    public function update(
        Staff $staff,
        Facility $facility,
        PharmacyMedicine $medicine,
        array $data
    ): PharmacyMedicine {
        $this->ensureFacilityMedicineAccess(
            $staff,
            $facility,
            $medicine
        );

        $medicine->update($data);

        return $medicine->fresh()->load('medicine');
    }

    public function delete(
        Staff $staff,
        Facility $facility,
        PharmacyMedicine $medicine
    ): void {
        $this->ensureFacilityMedicineAccess(
            $staff,
            $facility,
            $medicine
        );

        $medicine->delete();
    }

    private function ensureFacilityAccess(
        Staff $staff,
        Facility $facility
    ): void {
        $staff->facilityStaff()
            ->where('facility_id', $facility->id)
            ->firstOrFail();
    }

    private function ensureFacilityMedicineAccess(
        Staff $staff,
        Facility $facility,
        PharmacyMedicine $medicine
    ): void {
        $this->ensureFacilityAccess($staff, $facility);

        abort_unless(
            $medicine->facility_id === $facility->id,
            404
        );
    }

    public function stats(Staff $staff, Facility $facility): array
    {
        $this->ensureFacilityAccess($staff, $facility);

        $query = PharmacyMedicine::query()
            ->where('facility_id', $facility->id);

        return [
            'total_medicines' => (clone $query)->count(),

            'in_stock' => (clone $query)
                ->where('is_available', true)
                ->where('stock', '>', 0)
                ->count(),

            'out_of_stock' => (clone $query)
                ->where(function ($q) {
                    $q->where('stock', 0)
                        ->orWhere('is_available', false);
                })
                ->count(),
        ];

    }
}
