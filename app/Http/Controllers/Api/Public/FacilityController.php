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
            ->withCount(['facilityStaff as doctors_count', 'departments as departments_count'])
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
        ]);

        return new ShowFacilityResource($facility);
    }
}
