<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\SymptomCreated;
use App\Events\SymptomDeleted;
use App\Events\SymptomUpdated;
use App\Models\Specialization;
use App\Models\Symptom;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SymptomService
{
    public function paginate(
        int $perPage = 15,
        ?string $search = null,
        ?string $status = null,
        string $sortBy = 'created_at',
        string $orderBy = 'desc',
    ): LengthAwarePaginator {
        return Symptom::query()
            ->when(
                $search,
                fn ($q) => $q->where(function ($q) use ($search) {
                    $q->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%");
                })
            )
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy($sortBy, $orderBy)
            ->paginate($perPage);
    }

    public function show(Symptom $symptom): Symptom
    {
        return $symptom;
    }

    public function create(array $data): Symptom
    {
        $symptom = Symptom::create([
            'name' => $data['name'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        event(new SymptomCreated($symptom));

        return $symptom;
    }

    public function update(Symptom $symptom, array $data): Symptom
    {
        $symptom->update($data);

        $symptom = $symptom->fresh();

        event(new SymptomUpdated($symptom));

        return $symptom;
    }

    public function destroy(Symptom $symptom): void
    {
        $symptom->delete();

        event(new SymptomDeleted($symptom));
    }

    public function getStats(): array
    {
        $total = Symptom::count();
        $active = Symptom::where('is_active', true)->count();
        $inactive = Symptom::where('is_active', false)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];
    }

    public function getActive(?string $search = null, int $perPage = 50): LengthAwarePaginator
    {
        return Symptom::active()
            ->when(
                $search,
                fn ($q) => $q->where(function ($q) use ($search) {
                    $q->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%");
                })
            )
            ->orderBy('name->en')
            ->paginate($perPage);
    }

    public function getAssignedSymptomIds(Specialization $specialization): Collection
    {
        return $specialization->symptoms()->pluck('symptoms.id');
    }

    public function syncSpecializationSymptoms(Specialization $specialization, array $symptomIds): Specialization
    {
        $specialization->symptoms()->sync($symptomIds);

        return $specialization->load('symptoms');
    }
}
