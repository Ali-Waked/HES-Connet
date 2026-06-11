<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Enums\OrganizationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Requests\Organization\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationController extends Controller
{
    public function __construct(private readonly OrganizationService $organization_service) {}

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        $organizationType = OrganizationType::tryFrom(
            request('type', '')
        );

        return OrganizationResource::collection(
            $this->organization_service->paginate(
                (int) request('per_page', 15),
                request('search'),
                $organizationType
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrganizationRequest $request): Organization
    {
        $organization = $this->organization_service->create($request->validated());

        // info($request->all());

        return $organization;
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization): JsonResponse
    {
        $organization = $this->organization_service->show($organization);

        return response()->json([
            'data' => [
                'id' => $organization->id,
                'uuid' => $organization->uuid,
                'type' => $organization->type,
                'name' => $organization->getTranslations('name'),
                'description' => $organization->getTranslations('description'),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization): JsonResponse
    {
        $organization = $this->organization_service->update(
            $organization,
            $request->validated()
        );

        return response()->json([
            'message' => __('Organization updated successfully.'),
            'data' => new OrganizationResource($organization),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization): JsonResponse
    {
        $this->organization_service->destroy($organization);

        return response()->json([
            'message' => __('Organization deleted successfully.'),
        ]);
    }
}
