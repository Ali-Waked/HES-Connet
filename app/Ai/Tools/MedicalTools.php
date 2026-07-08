<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Facility;
use App\Models\Specialization;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Builder;

class GetDoctorsBySpecialtyTool extends BaseTool
{
    public function name(): string
    {
        return 'get_doctors_by_specialty';
    }

    public function description(): string
    {
        return 'Get doctors from the database filtered by specialty/specialization. Returns doctor name, specialty, consultation fee, and associated facilities.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'specialty' => [
                    'type' => 'string',
                    'description' => 'Medical specialty to filter by (e.g., "cardiology", "neurology", "dermatology", "orthopedics", "pediatrics")',
                ],
                'profession_id' => [
                    'type' => ['integer', 'string'],
                    'description' => 'Filter by profession ID',
                ],
                'facility_id' => [
                    'type' => ['integer', 'string'],
                    'description' => 'Filter by facility ID to get doctors at a specific facility',
                ],
                'limit' => [
                    'type' => ['integer', 'string'],
                    'description' => 'Maximum number of doctors to return',
                ],
            ],
        ];
    }

    public function handle(array $arguments): mixed
    {
        $query = Staff::query()
            ->with([
                'user:id,name,email',
                'specialization',
                'facilityStaff' => fn ($q) => $q->whereNull('ended_at')->with('facility:id,uuid,name,latitude,longitude,city_id'),
            ])
            ->whereHas('facilityStaff', fn (Builder $q) => $q
                ->whereNull('ended_at')
                ->whereHas('role', fn (Builder $r) => $r->where('slug', 'doctor_portal_user'))
            );

        if (! empty($arguments['specialty'])) {
            $term = mb_strtolower($arguments['specialty']);
            $query->whereHas('specialization', fn (Builder $q) => $q
                ->where('name->en', 'like', "%{$term}%")
                ->orWhere('name->ar', 'like', "%{$term}%")
            );
        }

        if (! empty($arguments['profession_id'])) {
            $query->where('profession_id', $arguments['profession_id']);
        }

        if (! empty($arguments['facility_id'])) {
            $query->whereHas('facilityStaff', fn (Builder $q) => $q
                ->where('facility_id', $arguments['facility_id'])
                ->whereNull('ended_at')
            );
        }

        $doctors = $query->limit(min($arguments['limit'] ?? 10, 50))->get();

        return $doctors->map(fn (Staff $doctor) => [
            'id' => $doctor->id,
            'uuid' => $doctor->uuid,
            'name' => $doctor->user?->name,
            'specialization' => $doctor->specialization?->getTranslations('name'),
            'experience_years' => $doctor->experience_years,
            'consultation_fee' => $doctor->consultation_fee,
            'facilities' => $doctor->facilityStaff->map(fn ($fs) => [
                'id' => $fs->facility?->id,
                'uuid' => $fs->facility?->uuid,
                'name' => $fs->facility?->name,
            ]),
        ])->toArray();
    }
}

class SearchSpecialtiesTool extends BaseTool
{
    public function name(): string
    {
        return 'search_specialties';
    }

    public function description(): string
    {
        return 'Search for medical specializations from the database. Matches patient symptoms to specializations through the symptoms-to-specialization relationship, or searches specializations by name. Returns real specializations with available doctor counts.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'symptoms' => [
                    'type' => 'string',
                    'description' => 'Patient symptoms description to find matching medical specializations',
                ],
                'search' => [
                    'type' => 'string',
                    'description' => 'Search for a specific specialization name (e.g., "cardiology", "neurology")',
                ],
                'limit' => [
                    'type' => ['integer', 'string'],
                    'description' => 'Maximum number of results to return',
                ],
            ],
        ];
    }

    public function handle(array $arguments): mixed
    {
        if (! empty($arguments['search'])) {
            $term = mb_strtolower($arguments['search']);

            $specializations = Specialization::query()
                ->where('name->en', 'like', "%{$term}%")
                ->orWhere('name->ar', 'like', "%{$term}%")
                ->withCount(['staff' => fn ($q) => $q->whereHas('facilityStaff', fn ($q2) => $q2
                    ->whereNull('ended_at')
                    ->whereHas('role', fn ($r) => $r->where('slug', 'doctor_portal_user'))
                )])
                ->limit(min($arguments['limit'] ?? 10, 50))
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'uuid' => $s->uuid,
                    'name' => $s->getTranslations('name'),
                    'description' => $s->getTranslations('description'),
                    'available_doctors' => $s->staff_count,
                ])
                ->values()
                ->toArray();

            return $specializations;
        }

        $symptoms = mb_strtolower($arguments['symptoms'] ?? '');

        if (empty($symptoms)) {
            return [];
        }

        $specializations = Specialization::query()
            ->with('symptoms')
            ->whereHas('symptoms', function ($q) use ($symptoms) {
                $q->where('name->en', 'like', "%{$symptoms}%")
                    ->orWhere('name->ar', 'like', "%{$symptoms}%");
            })
            ->withCount(['staff' => fn ($q) => $q->whereHas('facilityStaff', fn ($q2) => $q2
                ->whereNull('ended_at')
                ->whereHas('role', fn ($r) => $r->where('slug', 'doctor_portal_user'))
            )])
            ->limit(min($arguments['limit'] ?? 10, 50))
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'uuid' => $s->uuid,
                'name' => $s->getTranslations('name'),
                'description' => $s->getTranslations('description'),
                'available_doctors' => $s->staff_count,
                'symptoms' => $s->symptoms->map(fn ($sym) => $sym->getTranslations('name'))->values()->toArray(),
            ])
            ->values()
            ->toArray();

        return $specializations;
    }
}

class GetNearbyFacilitiesTool extends BaseTool
{
    public function name(): string
    {
        return 'get_nearby_facilities';
    }

    public function description(): string
    {
        return 'Get nearby medical facilities based on location. Returns facilities with their distance and available doctors.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'latitude' => [
                    'type' => ['number', 'string'],
                    'description' => 'Latitude of the location',
                ],
                'longitude' => [
                    'type' => ['number', 'string'],
                    'description' => 'Longitude of the location',
                ],
                'radius_km' => [
                    'type' => ['number', 'string'],
                    'description' => 'Search radius in kilometers',
                ],
                'facility_type' => [
                    'type' => 'string',
                    'description' => 'Filter by facility type',
                    'enum' => ['hospital', 'clinic', 'pharmacy', 'laboratory'],
                ],
                'limit' => [
                    'type' => ['integer', 'string'],
                    'description' => 'Maximum results to return',
                ],
            ],
        ];
    }

    public function handle(array $arguments): mixed
    {
        $lat = $arguments['latitude'] ?? null;
        $lng = $arguments['longitude'] ?? null;
        $radiusKm = $arguments['radius_km'] ?? 10;

        $query = Facility::query()
            ->with(['facilityStaff' => fn ($q) => $q
                ->whereNull('ended_at')
                ->whereHas('role', fn ($r) => $r->where('slug', 'doctor_portal_user'))
                ->with('staff.user:id,name'),
            ])
            ->selectRaw(
                '*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$lat, $lng, $lat]
            )
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');

        if (! empty($arguments['facility_type'])) {
            $query->where('facility_type', $arguments['facility_type']);
        }

        $facilities = $query->limit(min($arguments['limit'] ?? 10, 50))->get();

        return $facilities->map(fn ($facility) => [
            'id' => $facility->id,
            'uuid' => $facility->uuid,
            'name' => $facility->name,
            'type' => $facility->facility_type,
            'distance_km' => round($facility->distance, 2),
            'latitude' => $facility->latitude,
            'longitude' => $facility->longitude,
            'doctors_count' => $facility->facilityStaff->count(),
        ])->toArray();
    }
}
