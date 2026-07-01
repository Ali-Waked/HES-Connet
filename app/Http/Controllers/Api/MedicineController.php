<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Medicine\StoreMedicineRequest;
use App\Http\Requests\Medicine\UpdateMedicineRequest;
use App\Http\Resources\MedicineResource;
use App\Models\Medicine;
use App\Services\MedicineService;
use Illuminate\Http\JsonResponse;

class MedicineController extends Controller
{
    public function __construct(
        private readonly MedicineService $medicineService
    ) {}

    public function index(): JsonResponse
    {
        // $this->authorize('viewAny', Medicine::class);

        return response()->json(
            MedicineResource::collection(
                $this->medicineService->paginate(
                    (int) request('per_page', 15),
                    request('search'),
                    request('sort_by', 'created_at'),
                    request('sort_order', 'desc'),
                )
            )
        );
    }

    public function lookup(): JsonResponse
    {
        // $this->authorize('viewAny', Medicine::class);

        return response()->json(
            $this->medicineService->lookup(request('search'))
        );
    }

    public function show(Medicine $medicine): JsonResponse
    {
        // $this->authorize('view', Medicine::class);

        return response()->json([
            'data' => [
                'name' => $medicine->getTranslations('name'),
                'description' => $medicine->getTranslations('description'),
                'image_url' => $medicine->image_url,
            ],
        ]);
    }

    public function store(StoreMedicineRequest $request): JsonResponse
    {
        // $this->authorize('create', Medicine::class);

        $medicine = $this->medicineService->create($request->validated());

        return response()->json([
            'message' => __('Medicine created successfully.'),
            'data' => new MedicineResource($medicine),
        ], 201);
    }

    public function update(UpdateMedicineRequest $request, Medicine $medicine): JsonResponse
    {
        // $this->authorize('update', Medicine::class);

        $medicine = $this->medicineService->update($medicine, $request->validated());

        return response()->json([
            'message' => __('Medicine updated successfully.'),
            'data' => new MedicineResource($medicine),
        ]);
    }

    public function destroy(Medicine $medicine): JsonResponse
    {
        // $this->authorize('delete', Medicine::class);

        $this->medicineService->destroy($medicine);

        return response()->json([
            'message' => __('Medicine deleted successfully.'),
        ]);
    }
}
