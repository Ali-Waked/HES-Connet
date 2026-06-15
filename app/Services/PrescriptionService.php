<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Prescription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PrescriptionService
{
    public function paginate(
        int $perPage = 15,
        ?int $appointmentId = null,
    ): LengthAwarePaginator {
        return Prescription::query()
            ->with(['doctor.user', 'items.medicine'])
            ->when($appointmentId, fn ($query) => $query->where('appointment_id', $appointmentId))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Prescription
    {
        return Prescription::create($data);
    }

    public function show(Prescription $prescription): Prescription
    {
        return $prescription->load(['doctor.user', 'items.medicine', 'appointment']);
    }

    public function update(Prescription $prescription, array $data): Prescription
    {
        $prescription->update($data);

        return $prescription->refresh();
    }

    public function destroy(Prescription $prescription): void
    {
        $prescription->delete();
    }
}
