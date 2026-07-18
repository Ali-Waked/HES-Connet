<?php

declare(strict_types=1);

namespace App\Services\MedicalTriage;

use App\Models\Specialization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SpecialtyMatcherService
{
    private const FALLBACK_SPECIALTIES = [
        'General Practice',
        'Internal Medicine',
    ];

    public function findMatchingSpecialty(string $aiSpecialty): ?Specialization
    {
        $specialization = $this->searchExact($aiSpecialty);

        if ($specialization) {
            return $specialization;
        }

        $specialization = $this->searchPartial($aiSpecialty);

        if ($specialization) {
            return $specialization;
        }

        return $this->findFallback();
    }

    /**
     * @return Specialization[]
     */
    public function findMatchingSpecialties(string $aiSpecialty, int $limit = 3): array
    {
        $exact = $this->searchExact($aiSpecialty);

        if ($exact) {
            return [$exact];
        }

        $partial = $this->searchPartial($aiSpecialty, $limit);

        if ($partial->isNotEmpty()) {
            return $partial->toArray();
        }

        $fallback = $this->findFallback();

        return $fallback ? [$fallback] : [];
    }

    private function searchExact(string $name): ?Specialization
    {
        return Specialization::query()
            ->where('name->en', 'like', $name)
            ->orWhere('name->ar', 'like', $name)
            ->first();
    }

    private function searchPartial(string $name, int $limit = 1): Collection
    {
        $term = mb_strtolower(trim($name));

        return Specialization::query()
            ->where(function (Builder $q) use ($term) {
                $q->where('name->en', 'like', "%{$term}%")
                    ->orWhere('name->ar', 'like', "%{$term}%");
            })
            ->withCount(['staff' => fn (Builder $q) => $q->doctors()])
            ->orderBy('staff_count', 'desc')
            ->limit($limit)
            ->get();
    }

    private function findFallback(): ?Specialization
    {
        foreach (self::FALLBACK_SPECIALTIES as $fallback) {
            $specialization = $this->searchExact($fallback);

            if ($specialization) {
                return $specialization;
            }
        }

        return Specialization::query()
            ->withCount(['staff' => fn (Builder $q) => $q->doctors()])
            ->orderBy('staff_count', 'desc')
            ->first();
    }
}
