<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Staff\UpdateStaffSymptomsRequest;
use App\Http\Resources\FacilityStaffResource;
use App\Http\Resources\SymptomResource;
use App\Models\FacilityStaff;
use App\Services\SymptomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SymptomController extends Controller
{
    public function __construct(
        private readonly SymptomService $symptom_service,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return SymptomResource::collection(
            $this->symptom_service->getActive(
                search: request('search'),
                perPage: 200,
            )
        );
    }

    public function update(UpdateStaffSymptomsRequest $request, FacilityStaff $facility_staff): JsonResponse
    {
        $facilityStaff = $this->symptom_service->syncSymptoms(
            $facility_staff,
            $request->validated('symptom_ids'),
        );

        return response()->json([
            'message' => __('Symptoms updated successfully.'),
            'data' => new FacilityStaffResource($facilityStaff),
        ]);
    }
}
