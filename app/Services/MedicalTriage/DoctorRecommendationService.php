<?php

declare(strict_types=1);

namespace App\Services\MedicalTriage;

use App\DTOs\DoctorRecommendationDto;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DoctorRecommendationService
{
    public function __construct(
        private readonly SpecialtyMatcherService $specialtyMatcher,
    ) {}

    /**
     * Find matching doctors for a given specialty. Falls back to parent/general specialties.
     *
     * @return DoctorRecommendationDto[]
     */
    public function findDoctors(string $specialty, int $limit = 10): array
    {
        $doctors = $this->searchBySpecialty($specialty, $limit);

        if ($doctors->isNotEmpty()) {
            return $this->mapToDtos($doctors);
        }

        $specializations = $this->specialtyMatcher->findMatchingSpecialties($specialty, 3);

        foreach ($specializations as $spec) {
            $doctors = $this->searchBySpecializationId($spec->id, $limit);

            if ($doctors->isNotEmpty()) {
                return $this->mapToDtos($doctors);
            }
        }

        return $this->getFallbackDoctors($limit);
    }

    private function searchBySpecialty(string $specialty, int $limit): Collection
    {
        $term = mb_strtolower(trim($specialty));

        return $this->baseDoctorQuery()
            ->whereHas('specialization', function (Builder $q) use ($term) {
                $q->where('name->en', 'like', "%{$term}%")
                    ->orWhere('name->ar', 'like', "%{$term}%");
            })
            ->limit($limit)
            ->get();
    }

    private function searchBySpecializationId(int $specializationId, int $limit): Collection
    {
        return $this->baseDoctorQuery()
            ->where('specialization_id', $specializationId)
            ->limit($limit)
            ->get();
    }

    private function getFallbackDoctors(int $limit): array
    {
        $fallbackSpecializations = ['General Practice', 'Internal Medicine'];

        foreach ($fallbackSpecializations as $fallback) {
            $doctors = $this->searchBySpecialty($fallback, $limit);

            if ($doctors->isNotEmpty()) {
                return $this->mapToDtos($doctors);
            }
        }

        $doctors = $this->baseDoctorQuery()
            ->limit($limit)
            ->get();

        return $this->mapToDtos($doctors);
    }

    private function baseDoctorQuery(): Builder
    {
        return Staff::query()
            ->with([
                'user:id,name,email',
                'user.profile',
                'specialization',
                'departments',
                'facilityStaff' => fn ($q) => $q
                    ->whereNull('ended_at')
                    ->with('facility:id,uuid,name')
                    ->with('role:id,slug,name'),
            ])
            ->where('status', 'active')
            ->whereHas('facilityStaff', function (Builder $q) {
                $q->whereNull('ended_at')
                    ->whereHas('role', fn (Builder $r) => $r->where('slug', 'doctor_portal_user'));
            });
    }

    private function mapToDtos(Collection $doctors): array
    {
        $dtos = [];
        $seen = [];

        foreach ($doctors as $doctor) {
            if (in_array($doctor->uuid, $seen, true)) {
                continue;
            }
            $seen[] = $doctor->uuid;

            $activeFacilityStaff = $doctor->facilityStaff->first(fn ($fs) => $fs->role?->slug === 'doctor_portal_user');

            if (! $activeFacilityStaff) {
                continue;
            }

            $dtos[] = DoctorRecommendationDto::fromStaff(
                staff: $doctor,
                facilityName: $activeFacilityStaff->facility?->getTranslation('name', app()->getLocale()) ?? '',
                facilityUuid: $activeFacilityStaff->facility?->uuid ?? '',
            );
        }

        return $dtos;
    }
}
