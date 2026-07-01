<?php

namespace App\Services;

use App\Events\PrescriptionCreated;
use App\Models\Appointment;
use App\Models\Facility;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\Staff;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PrescriptionService
{
    public function __construct(private readonly UuidResolver $uuid_resolver) {}

    public function paginate(
        Staff $staff,
        Facility $facility,
        int $perPage = 15,
        ?string $search = null,
    ): LengthAwarePaginator {

        $facilityStaff = $staff
            ->facilityStaff()
            ->where('facility_id', $facility->id)
            ->firstOrFail();
        $isOwner = $facilityStaff->role->slug === 'facility_admin';

        return Prescription::query()
            ->whereHas(
                'appointment.facilityStaff',
                fn ($q) => $q
                    ->where('facility_id', $facility->id)
                    ->when(! $isOwner, fn ($que) => $que->where('staff_id', $staff->id))
            )

            ->when(
                $search,
                fn ($q) => $q->whereHas(
                    'appointment.patient.user',
                    fn ($patient) => $patient->where(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                )
            )

            ->with([
                'appointment.patient.user',
                'appointment.facilityStaff.staff.user',
                'items.medicine',
            ])

            ->latest()
            ->paginate($perPage);
    }

    public function create(Staff $staff, Facility $facility, array $data): Prescription
    {
        $staff->facilityStaff()
            ->where('facility_id', $facility->id)
            ->firstOrFail();

        $appointmentId = $this->uuid_resolver->resolve(
            Appointment::class,
            $data['appointment_id']
        );

        return DB::transaction(function () use ($data, $appointmentId) {

            $prescription = Prescription::create([
                'appointment_id' => $appointmentId,
                'notes' => $data['notes'] ?? null,
            ]);

            event(new PrescriptionCreated($prescription));

            $prescription->items()->createMany(
                collect($data['medicines'])
                    ->map(fn ($item) => [
                        'medicine_id' => $this->uuid_resolver->resolve(Medicine::class, $item['medicine_id']),
                        'dosage' => $item['dosage'],
                        'frequency' => $item['frequency'] ?? null,
                        'duration' => $item['duration'] ?? null,
                        'route' => $item['route'] ?? null,
                        'instructions' => $item['instructions'] ?? null,
                    ])
                    ->toArray()
            );

            return $prescription;
        });
    }

    public function show(Facility $facility, Prescription $prescription)
    {
        auth()->user()->staff->facilityStaff()->where('facility_id', $facility->id)->firstOrFail();

        return $prescription->load(['appointment.facilityStaff.staff.user', 'items.medicine', 'appointment.patient.user']);

    }
}
