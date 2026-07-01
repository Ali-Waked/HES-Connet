<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Facility;
use App\Models\Profession;
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
                    'type' => 'integer',
                    'description' => 'Filter by profession ID',
                ],
                'facility_id' => [
                    'type' => 'integer',
                    'description' => 'Filter by facility ID to get doctors at a specific facility',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of doctors to return',
                    'default' => 10,
                ],
            ],
        ];
    }

    public function handle(array $arguments): mixed
    {
        $query = Staff::query()
            ->with([
                'user:id,name,email,avatar',
                'facilityStaff' => fn ($q) => $q->whereNull('ended_at')->with('facility:id,uuid,name,latitude,longitude, city_id'),
            ])
            ->whereHas('facilityStaff', fn (Builder $q) => $q
                ->whereNull('ended_at')
                ->whereHas('role', fn (Builder $r) => $r->where('slug', 'doctor_portal_user'))
            );

        if (! empty($arguments['specialty'])) {
            $query->where(function (Builder $q) use ($arguments) {
                $term = $arguments['specialty'];
                $q->where('specialization', 'like', "%{$term}%");
            });
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
            'specialization' => $doctor->specialization,
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
        return 'Search for medical specialties/professions. Maps symptoms to possible medical specialties and returns matching professions from the database.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'symptoms' => [
                    'type' => 'string',
                    'description' => 'Patient symptoms description to match against specialties',
                ],
                'search' => [
                    'type' => 'string',
                    'description' => 'Search for specific specialty or profession name',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of specialties to return',
                    'default' => 10,
                ],
            ],
        ];
    }

    public function handle(array $arguments): mixed
    {
        $symptomToSpecialtyMap = [
            'chest pain' => ['Cardiology', 'Pulmonology'],
            'shortness of breath' => ['Cardiology', 'Pulmonology'],
            'palpitations' => ['Cardiology'],
            'high blood pressure' => ['Cardiology'],
            'headache' => ['Neurology', 'General Medicine'],
            'dizziness' => ['Neurology', 'ENT'],
            'migraine' => ['Neurology'],
            'seizure' => ['Neurology'],
            'numbness' => ['Neurology'],
            'skin rash' => ['Dermatology'],
            'skin infection' => ['Dermatology'],
            'acne' => ['Dermatology'],
            'hair loss' => ['Dermatology'],
            'joint pain' => ['Orthopedics', 'Rheumatology'],
            'back pain' => ['Orthopedics', 'Neurosurgery'],
            'fracture' => ['Orthopedics'],
            'swelling' => ['Orthopedics', 'General Medicine'],
            'fever' => ['General Medicine', 'Infectious Disease'],
            'cough' => ['Pulmonology', 'General Medicine'],
            'sore throat' => ['ENT', 'General Medicine'],
            'ear pain' => ['ENT'],
            'hearing loss' => ['ENT'],
            'abdominal pain' => ['Gastroenterology', 'General Medicine'],
            'nausea' => ['Gastroenterology', 'General Medicine'],
            'vomiting' => ['Gastroenterology', 'General Medicine'],
            'diarrhea' => ['Gastroenterology', 'General Medicine'],
            'constipation' => ['Gastroenterology'],
            'blurred vision' => ['Ophthalmology', 'Neurology'],
            'eye pain' => ['Ophthalmology'],
            'vision loss' => ['Ophthalmology'],
            'frequent urination' => ['Urology', 'Nephrology'],
            'blood in urine' => ['Urology', 'Nephrology'],
            'kidney pain' => ['Nephrology', 'Urology'],
            'anxiety' => ['Psychiatry', 'Psychology'],
            'depression' => ['Psychiatry', 'Psychology'],
            'insomnia' => ['Psychiatry', 'General Medicine'],
            'pregnancy' => ['Obstetrics & Gynecology'],
            'menstrual pain' => ['Obstetrics & Gynecology'],
            'vaginal discharge' => ['Obstetrics & Gynecology'],
            'allergy' => ['Immunology', 'General Medicine'],
            'asthma' => ['Pulmonology'],
            'diabetes' => ['Endocrinology'],
            'thyroid' => ['Endocrinology'],
            'weight loss' => ['Endocrinology', 'General Medicine'],
            'fatigue' => ['General Medicine', 'Endocrinology'],
            'anemia' => ['Hematology', 'General Medicine'],
            'bleeding' => ['Hematology', 'General Medicine'],
            'cancer' => ['Oncology'],
            'lump' => ['Oncology', 'General Surgery'],
        ];

        if (! empty($arguments['search'])) {
            $professions = Profession::query()
                ->where('name', 'like', "%{$arguments['search']}%")
                ->orWhere('slug', 'like', "%{$arguments['search']}%")
                ->limit(min($arguments['limit'] ?? 10, 50))
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'description' => $p->description,
                ])
                ->toArray();

            return $professions;
        }

        $symptoms = mb_strtolower($arguments['symptoms'] ?? '');
        $matchedSpecialties = [];

        foreach ($symptomToSpecialtyMap as $keyword => $specialties) {
            if (str_contains($symptoms, $keyword)) {
                $matchedSpecialties = array_merge($matchedSpecialties, $specialties);
            }
        }

        $matchedSpecialties = array_unique($matchedSpecialties);

        if (empty($matchedSpecialties)) {
            $matchedSpecialties = ['General Medicine'];
        }

        $professions = Profession::query()
            ->where(function ($q) use ($matchedSpecialties) {
                foreach ($matchedSpecialties as $specialty) {
                    $q->orWhere('slug', 'like', "%{$specialty}%")
                        ->orWhere('name', 'like', "%{$specialty}%");
                }
            })
            ->limit(min($arguments['limit'] ?? 10, 50))
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'description' => $p->description,
            ])
            ->toArray();

        if (empty($professions)) {
            return array_map(fn ($s) => [
                'id' => null,
                'name' => $s,
                'slug' => str_replace([' & ', ' '], ['-', '-'], mb_strtolower($s)),
                'description' => null,
            ], $matchedSpecialties);
        }

        return $professions;
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
                    'type' => 'number',
                    'description' => 'Latitude of the location',
                ],
                'longitude' => [
                    'type' => 'number',
                    'description' => 'Longitude of the location',
                ],
                'radius_km' => [
                    'type' => 'number',
                    'description' => 'Search radius in kilometers',
                    'default' => 10,
                ],
                'facility_type' => [
                    'type' => 'string',
                    'description' => 'Filter by facility type',
                    'enum' => ['hospital', 'clinic', 'pharmacy', 'laboratory'],
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum results to return',
                    'default' => 10,
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
