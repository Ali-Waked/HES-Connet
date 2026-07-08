<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\SpecializationCreated;
use App\Events\SpecializationDeleted;
use App\Events\SpecializationUpdated;
use App\Models\Specialization;
use App\Models\Symptom;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SpecializationService
{
    public function lookup(): Collection
    {
        return Specialization::query()
            ->orderBy('name->en')
            ->get();
    }

    public function paginate(
        int $perPage = 15,
        ?string $search = null,
        string $sortBy = 'created_at',
        string $orderBy = 'desc',
    ): LengthAwarePaginator {
        return Specialization::query()
            ->when(
                $search,
                fn ($q) => $q->where(function ($q) use ($search) {
                    $q->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%");
                })
            )
            ->withCount('symptoms')
            ->orderBy($sortBy, $orderBy)
            ->paginate($perPage);
    }

    public function show(Specialization $specialization): Specialization
    {
        return $specialization->loadCount('symptoms')->load('symptoms');
    }

    public function create(array $data): Specialization
    {
        $specialization = Specialization::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        event(new SpecializationCreated($specialization));

        return $specialization->loadCount('symptoms');
    }

    public function update(Specialization $specialization, array $data): Specialization
    {
        $specialization->update($data);

        $specialization = $specialization->fresh()->loadCount('symptoms');

        event(new SpecializationUpdated($specialization));

        return $specialization;
    }

    public function destroy(Specialization $specialization): void
    {
        $specialization->delete();

        event(new SpecializationDeleted($specialization));
    }

    public function listSymptoms(Specialization $specialization): Collection
    {
        return $specialization->symptoms()->orderBy('name->en')->get();
    }

    public function syncSymptoms(Specialization $specialization, array $symptomIds): Specialization
    {
        $specialization->symptoms()->sync($symptomIds);

        return $specialization->loadCount('symptoms')->load('symptoms');
    }

    public function attachSymptoms(Specialization $specialization, array $symptomIds): Specialization
    {
        $specialization->symptoms()->syncWithoutDetaching($symptomIds);

        return $specialization->loadCount('symptoms')->load('symptoms');
    }

    public function detachSymptom(Specialization $specialization, Symptom $symptom): Specialization
    {
        $specialization->symptoms()->detach($symptom->id);

        return $specialization->loadCount('symptoms')->load('symptoms');
    }
}
