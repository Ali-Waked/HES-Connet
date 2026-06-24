<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MedicationRequest;
use App\Services\DashboardAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardAnalyticsController extends Controller
{
    public function __construct(
        private readonly DashboardAnalyticsService $dashboardAnalyticsService,
    ) {}

    public function dashboard(): JsonResponse
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
