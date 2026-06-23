<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\City\StoreCityRequest;
use App\Http\Requests\City\UpdateCityRequest;
use App\Http\Resources\CityResource;
use App\Models\City;
use App\Services\CityService;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    public function __construct(
        private readonly CityService $city_service
    ) {}

    public function index()
    {
        return CityResource::collection(
            $this->city_service->paginate(
                (int) request('per_page', 15),
                request('search'),
                request('is_active') !== null ? filter_var(request('is_active'), FILTER_VALIDATE_BOOLEAN) : null
            )
        );
    }

    public function store(StoreCityRequest $request): JsonResponse
    {
        $city = $this->city_service->create(
            $request->validated()
        );

        return response()->json([
            'message' => __('City created successfully.'),
            'data' => new CityResource($city),
        ], 201);
    }

    public function show(City $city): JsonResponse
    {
        return response()->json([
              'data' => [
                'name' => $city->getTranslations('name'),
                'is_active'=> $city->is_active,
            ]
        ]);
    }

    public function update(UpdateCityRequest $request, City $city): JsonResponse
    {
        $city = $this->city_service->update(
            $city,
            $request->validated()
        );

        return response()->json([
            'message' => __('City updated successfully.'),
            'data' => new CityResource($city),
        ]);
    }

    public function destroy(City $city): JsonResponse
    {
        $this->city_service->destroy($city);

        return response()->json([
            'message' => __('City deleted successfully.'),
        ]);
    }
}
