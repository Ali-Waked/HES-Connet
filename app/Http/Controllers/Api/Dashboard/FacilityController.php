<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facility\StoreFacilityRequest;
use App\Http\Requests\Facility\UpdateFacilityRequest;
use App\Models\Facility;
use App\Services\FacilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class FacilityController extends Controller
{
    public function __construct(private readonly FacilityService $facility_service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): LengthAwarePaginator
    {
        return $this->facility_service->paginate(
                request('per_page', 15),
                request('search'),
                request('facility_type')
           );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFacilityRequest $request):JsonResponse
    {
        $facility = $this->facility_service->create($request->validated());
        return response()->json([
            'message' => __('Facility created successfully.'),
            'facility' => $facility,
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Facility $facility):Facility
    {
        return $this->facility_service->show($facility);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFacilityRequest $request, Facility $facility):JsonResponse
    {
        $facility = $this->facility_service->update(
            $facility,
            $request->validated()
        );

        return response()->json([
            'message' => __('Facility updated successfully.'),
            'data' => $facility
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facility $facility):JsonResponse
    {
        $this->facility_service->destroy($facility);

        return response()->json([
            'message' => __('Facility deleted successfully.'),
        ]);
    }
}
