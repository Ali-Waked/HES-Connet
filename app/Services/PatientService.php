<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PatientService
{
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return Patient::query()
            ->with('user')
            ->when($search, fn ($query) => $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")))
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(array $data): Patient
    {
        return Patient::create($data);
    }

    public function show(Patient $patient): Patient
    {
        return $patient->load('user');
    }

    public function update(Patient $patient, array $data): Patient
    {
        $patient->update($data);

        return $patient->refresh();
    }

    public function destroy(Patient $patient): void
    {
        $patient->delete();
    }
}
