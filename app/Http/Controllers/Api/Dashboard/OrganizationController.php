<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Requests\Organization\UpdateOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class OrganizationController extends Controller
{
    public function __construct(private readonly OrganizationService $organization_service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): LengthAwarePaginator
    {
        return $this->organization_service->paginate(request('per_page', 15),request('search'),);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrganizationRequest $request):JsonResponse
    {
        $organization = $this->organization_service->create($request->validated());
        return response()->json([
            'message' => __('Organization created successfully.'),
            'organization' => $organization,
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization):Organization
    {
        return $this->organization_service->show($organization);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization):JsonResponse
    {
        $this->organization_service->update($organization,$request->validated());
        return response()->json([
            'message' => __('Organization updated successfully.'),
            'organization' => $organization
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization):JsonResponse
    {
        $this->organization_service->destroy($organization);
        return response()->json([
            'message'=> __('Organization deleted successfully'),
        ]);
    }
}
