<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Http\Resources\PharmacyMedicineResource;
use App\Models\Facility;
use App\Models\Medicine;
use App\Models\PharmacyMedicine;
use App\Services\FacilityPortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MedicineController extends Controller
{
    public function __construct(
        private readonly FacilityPortalService $portalService,
    ) {}

    public function index(
        Request $request,
        Facility $facility
    ): AnonymousResourceCollection {
        $medicines = $this->portalService->paginate(
            auth()->user()->staff,
            $facility,
            $request->integer('per_page', 15),
            $request->string('search')->toString()
        );

        return PharmacyMedicineResource::collection($medicines);
    }

    public function show(
        Facility $facility,
        PharmacyMedicine $medicine
    ): PharmacyMedicineResource {
        return new PharmacyMedicineResource(
            $this->portalService->show(
                auth()->user()->staff,
                $facility,
                $medicine
            )
        );
    }

    public function store(
        Request $request,
        Facility $facility
    ): JsonResponse {
        $data = $request->validate([
            'medicine_uuid' => ['required', 'exists:medicines,uuid'],
            'stock' => ['required', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_available' => ['boolean'],
        ]);

        $medicine = $this->portalService->store(
            auth()->user()->staff,
            $facility,
            $data
        );

        return response()->json([
            'message' => 'Medicine added successfully.',
            'data' => new PharmacyMedicineResource($medicine),
        ], 201);
    }

    public function update(
        Request $request,
        Facility $facility,
        PharmacyMedicine $medicine
    ): JsonResponse {
        $data = $request->validate([
            'stock' => ['sometimes', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_available' => ['sometimes', 'boolean'],
        ]);

        $medicine = $this->portalService->update(
            auth()->user()->staff,
            $facility,
            $medicine,
            $data
        );

        return response()->json([
            'message' => 'Medicine updated successfully.',
            'data' => new PharmacyMedicineResource($medicine),
        ]);
    }

    public function destroy(
        Facility $facility,
        PharmacyMedicine $medicine
    ): JsonResponse {
        $this->portalService->delete(
            auth()->user()->staff,
            $facility,
            $medicine
        );

        return response()->json([
            'message' => 'Medicine deleted successfully.',
        ]);
    }

    public function getAllMedicine(Facility $facility)
    {
        $search = request('search');

        return Medicine::query()
            ->select(['uuid', 'name', 'image_url'])
            ->whereDoesntHave('pharmacyMedicines', function ($query) use ($facility) {
                $query->where('facility_id', $facility->id);
            })
            ->when(
                $search,
                fn ($query) => $query->where('name', 'like', "%{$search}%")
            )
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    public function stats(Facility $facility): JsonResponse
    {
        $stats = $this->portalService->stats(
            auth()->user()->staff,
            $facility
        );

        return response()->json([
            'data' => $stats,
        ]);
    }
}
