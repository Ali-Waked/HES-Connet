<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Medicine\StoreMedicineRequest;
use App\Http\Requests\Medicine\UpdateMedicineRequest;
use App\Http\Resources\MedicationRequestResource;
use App\Http\Resources\MedicineResource;
use App\Http\Resources\PrescriptionCollection;
use App\Http\Resources\PrescriptionResource;
use App\Models\MedicationRequest;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Services\DashboardAnalyticsService;
use App\Services\MedicineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function __construct(
        private readonly MedicineService $medicineService,
        private readonly DashboardAnalyticsService $dashboardAnalyticsService,
    ) {}

    public function medicinesIndex(Request $request): JsonResponse
    {
        $medicines = $this->medicineService->paginate(
            (int) $request->get('per_page', 15),
            $request->get('search'),
            $request->get('sort_by', 'created_at'),
            $request->get('sort_order', 'desc'),
        );

        return response()->json([
            'data' => MedicineResource::collection($medicines),
            'meta' => [
                'current_page' => $medicines->currentPage(),
                'last_page' => $medicines->lastPage(),
                'per_page' => $medicines->perPage(),
                'total' => $medicines->total(),
            ],
        ]);
    }

    public function medicinesStore(StoreMedicineRequest $request): JsonResponse
    {
        $medicine = $this->medicineService->create($request->validated());

        return response()->json([
            'message' => __('Medicine created successfully.'),
            'data' => new MedicineResource($medicine),
        ], 201);
    }

    public function medicinesShow(Medicine $medicine): JsonResponse
    {
        return response()->json([
            'data' => new MedicineResource($medicine),
        ]);
    }

    public function medicinesUpdate(UpdateMedicineRequest $request, Medicine $medicine): JsonResponse
    {
        $medicine = $this->medicineService->update($medicine, $request->validated());

        return response()->json([
            'message' => __('Medicine updated successfully.'),
            'data' => new MedicineResource($medicine),
        ]);
    }

    public function medicinesDestroy(Medicine $medicine): JsonResponse
    {
        $this->medicineService->destroy($medicine);

        return response()->json([
            'message' => __('Medicine deleted successfully.'),
        ]);
    }

    public function prescriptions(Request $request): PrescriptionCollection
    {
        $prescriptions = Prescription::query()
            ->with(['appointment.facilityStaff.staff.user', 'items.medicine', 'appointment.patient.user'])
            ->latest()
            ->paginate((int) $request->get('per_page', 15));

        return new PrescriptionCollection($prescriptions);
    }

    public function medicationRequests(Request $request): JsonResponse
    {
        $requests = MedicationRequest::query()
            ->with(['patient.user', 'facility', 'prescription', 'pharmacist.user'])
            ->when(
                $request->get('status'),
                fn ($q, $status) => $q->where('status', $status)
            )
            ->latest()
            ->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'data' => MedicationRequestResource::collection($requests),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    public function analytics(): JsonResponse
    {
        return response()->json(
            $this->dashboardAnalyticsService->getDashboard()
        );
    }
}
