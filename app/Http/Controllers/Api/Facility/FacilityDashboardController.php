<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Http\Resources\Facility\AlertResource;
use App\Http\Resources\Facility\AnalyticsResource;
use App\Http\Resources\Facility\DashboardResource;
use App\Http\Resources\Facility\DoctorPerformanceResource;
use App\Http\Resources\Facility\LiveAppointmentResource;
use App\Http\Resources\Facility\PatientOverviewResource;
use App\Http\Resources\Facility\ScheduleOverviewResource;
use App\Services\FacilityDashboardService;
use App\Services\FacilityPortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacilityDashboardController extends Controller
{
    public function __construct(
        private readonly FacilityDashboardService $dashboard_service,
        private readonly FacilityPortalService $portal_service,
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        $facility = $this->portal_service->resolveFacility($request);
        $user = $request->user();

        $data = $this->portal_service->getDashboard($facility, $user);

        return response()->json($data);
    }

    public function liveAppointments(Request $request): JsonResponse
    {
        $facility = $this->portal_service->resolveFacility($request);

        $appointments = $this->dashboard_service->getLiveAppointments($facility->id);

        return response()->json(
            LiveAppointmentResource::collection($appointments)
        );
    }

    public function doctorsPerformance(Request $request): JsonResponse
    {
        $facility = $this->portal_service->resolveFacility($request);

        $performance = $this->dashboard_service->getDoctorPerformance($facility->id);

        return response()->json(
            DoctorPerformanceResource::collection($performance)
        );
    }

    public function patients(Request $request): JsonResponse
    {
        $facility = $this->portal_service->resolveFacility($request);

        $overview = $this->dashboard_service->getPatientOverview($facility->id);

        return response()->json(new PatientOverviewResource($overview));
    }

    public function staff(Request $request): JsonResponse
    {
        $facility = $this->portal_service->resolveFacility($request);

        $staff = $this->dashboard_service->getStaff($facility->id);

        return response()->json($staff);
    }

    public function schedules(Request $request): JsonResponse
    {
        $facility = $this->portal_service->resolveFacility($request);

        $schedules = $this->dashboard_service->getScheduleOverview($facility->id);

        return response()->json(new ScheduleOverviewResource($schedules));
    }

    public function analytics(Request $request): JsonResponse
    {
        $facility = $this->portal_service->resolveFacility($request);

        $analytics = $this->dashboard_service->getAnalytics($facility->id);

        return response()->json(new AnalyticsResource($analytics));
    }

    public function alerts(Request $request): JsonResponse
    {
        $facility = $this->portal_service->resolveFacility($request);

        $alerts = $this->dashboard_service->getAlerts($facility->id);

        return response()->json(
            AlertResource::collection($alerts)
        );
    }
}
