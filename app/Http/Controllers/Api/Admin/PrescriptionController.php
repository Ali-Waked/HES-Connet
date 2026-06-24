<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MedicationRequestResource;
use App\Http\Resources\PrescriptionCollection;
use App\Models\MedicationRequest;
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

    public function requestAnalytics(Request $request): JsonResponse
    {
        $requestsQuery = MedicationRequest::query();

        // ---------------------------
        // 1. Basic Counters
        // ---------------------------
        $totalRequests = (clone $requestsQuery)->count();

        $pendingRequests = (clone $requestsQuery)
            ->where('status', 'pending')
            ->count();

        $approvedRequests = (clone $requestsQuery)
            ->where('status', 'approved')
            ->count();

        $rejectedRequests = (clone $requestsQuery)
            ->where('status', 'rejected')
            ->count();

        $approvalRate = $totalRequests > 0
            ? round(($approvedRequests / $totalRequests) * 100)
            : 0;

        // ---------------------------
        // 2. Requests Per Month (All Facilities)
        // ---------------------------
        $requestsPerMonth = $requestsQuery
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // ---------------------------
        // 3. Status Distribution
        // ---------------------------
        $statusDistribution = $requestsQuery
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // ---------------------------
        // 4. Most Requested Medicines (Global)
        // ---------------------------
        $mostRequestedMedicines = MedicationRequest::query()
            ->join('prescriptions', 'prescriptions.id', '=', 'medication_requests.prescription_id')
            ->join('prescription_items', 'prescription_items.prescription_id', '=', 'prescriptions.id')
            ->join('medicines', 'medicines.id', '=', 'prescription_items.medicine_id')
            ->selectRaw('medicines.name, COUNT(*) as total')
            ->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ---------------------------
        // 5. Top Pharmacies (ALL FACILITIES)
        // ---------------------------
        $topPharmacies = MedicationRequest::query()
            ->join('facilities', 'facilities.id', '=', 'medication_requests.facility_id')
            ->selectRaw('facilities.name, COUNT(*) as total')
            ->groupBy('facilities.id', 'facilities.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ---------------------------
        // 6. Recent Requests (Global)
        // ---------------------------
        $recentRequests = MedicationRequest::query()
            ->with(['patient.user', 'facility', 'prescription'])
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'data' => [
                'total_requests' => $totalRequests,
                'pending_requests' => $pendingRequests,
                'approved_requests' => $approvedRequests,
                'rejected_requests' => $rejectedRequests,
                'approval_rate' => $approvalRate,

                'requests_per_month' => $requestsPerMonth,
                'status_distribution' => $statusDistribution,
                'most_requested_medicines' => $mostRequestedMedicines,
                'top_pharmacies' => $topPharmacies,
                'recent_requests' => $recentRequests,
            ],
        ]);
    }
}
