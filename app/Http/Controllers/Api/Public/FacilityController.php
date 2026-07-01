<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\Facility\FacilityRequest;
use App\Http\Resources\Public\FacilityCollection;
use App\Http\Resources\Public\ShowFacilityResource;
use App\Models\Facility;

class FacilityController extends Controller
{
    public function index(FacilityRequest $request): FacilityCollection
    {
        $facilities = Facility::query()
            ->with(['organization', 'headStaff', 'facilityImages'])
            ->addSelect([
                'doctors_count' => fn ($q) => $q->from('facility_staff')
                    ->whereColumn('facility_id', 'facilities.id')
                    ->selectRaw('COUNT(*)'),
                'departments_count' => fn ($q) => $q->from('facility_staff')
                    ->whereColumn('facility_id', 'facilities.id')
                    ->whereNotNull('department_id')
                    ->selectRaw('COUNT(DISTINCT department_id)'),
            ])
            ->when(
                $request->search,
                fn ($query, $search) => $query->where(function ($q) use ($search) {
                    $q->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%")
                        ->orWhere('description->en', 'like', "%{$search}%")
                        ->orWhere('description->ar', 'like', "%{$search}%");
                })
            )
            ->when(
                $request->facility_type,
                fn ($query, $type) => $query->where('facility_type', $type)
            )
            ->latest()
            ->paginate($request->per_page ?? 15);

        return new FacilityCollection($facilities);
    }

    public function show(Facility $facility): ShowFacilityResource
    {
        $facility->load([
            'organization',
            'headStaff',
            'facilityImages',
            'facilityDocuments',
            'departments',
            'facilityStaff.staff.user',
            'publicReviews.patient.user',
        ]);

        $facility->loadCount(['publicReviews as reviews_count']);
        $facility->loadAggregate(['publicReviews as average_rating'], 'rating', 'avg');

        return new ShowFacilityResource($facility);
    }
}
