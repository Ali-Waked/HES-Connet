<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facility\StoreFacilityRequest;
use App\Http\Requests\Facility\UpdateFacilityRequest;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use App\Services\FacilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityController extends Controller
{
    public function __construct(private readonly FacilityService $facility_service) {}

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): JsonResource
    {
        return FacilityResource::collection(
            $this->facility_service->paginate(
                (int) request('per_page', 15),
                request('search'),
                request('facility_type')
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFacilityRequest $request): Facility
    {
        $facility = $this->facility_service->create($request->validated());

        return $facility;
    }

    /**
     * Display the specified resource.
     */
    public function show(Facility $facility): FacilityResource
    {
        $facility->load([
            'organization',
            'owner',
            'createdBy',
            'city',
            'parent',
            'children',
            'facilityImages',
            'facilityDocuments',
            'departments',
        ]);

        return new FacilityResource($facility);
    }

    /**
     * Display the specified resource for editing.
     */
    public function edit(Facility $facility): JsonResponse
    {
        $facility = $this->facility_service->show($facility);

        return response()->json([
            'data' => [
                'id' => $facility->id,
                'uuid' => $facility->uuid,
                'status' => $facility->status->value,
                'facility_type' => $facility->facility_type->value,
                'organization_id' => $facility->organization?->uuid,
                'approval_status' => $facility->approval_status->value,
                'latitude' => $facility->latitude,
                'longitude' => $facility->longitude,
                'name' => $facility->getTranslations('name'),
                'description' => $facility->getTranslations('description'),
                'owner' => $facility->owner,
                'cover_image' => $facility->cover_image,
                'images' => $facility->facilityImages,
                'files' => $facility->facilityDocuments,
                'is_featured' => $facility->is_featured,
                'city' => $facility->city ? [
                    'uuid' => $facility->city->uuid,
                    'name' => $facility->city->getTranslations('name'),
                ] : null,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFacilityRequest $request, Facility $facility): JsonResponse
    {
        $facility = $this->facility_service->update(
            $facility,
            $request->validated()
        );

        return response()->json([
            'message' => __('Facility updated successfully.'),
            'data' => new FacilityResource($facility),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facility $facility): JsonResponse
    {
        $this->facility_service->destroy($facility);

        return response()->json([
            'message' => __('Facility deleted successfully.'),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json(
            $this->facility_service->getStats()
        );
    }
}
