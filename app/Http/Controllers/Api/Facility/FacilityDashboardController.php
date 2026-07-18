<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Facility\FacilityDashboardResource;
use App\Http\Responses\ApiResponse;
use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Services\NewFacilityDashboardService;
use Illuminate\Http\JsonResponse;

class FacilityDashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly NewFacilityDashboardService $dashboardService,
    ) {}

    public function overview(Facility $facility): JsonResponse
    {
        $facilityStaff = $this->resolveFacilityStaff($facility);

        if (! $facilityStaff) {
            return $this->unauthorized();
        }

        $staffId = $facilityStaff->is_owner ? null : $facilityStaff->id;

        return $this->respond(
            new FacilityDashboardResource([
                'cards' => $this->dashboardService->getCards($facility, $staffId),
                'growth_percentages' => $this->dashboardService->getGrowthPercentages($facility, $staffId),
                'recent_data' => $this->dashboardService->getRecentData($facility, $staffId),
                'charts' => $this->dashboardService->getCharts($facility, $staffId),
            ])
        );
    }

    public function alerts(Facility $facility): JsonResponse
    {
        $facilityStaff = $this->resolveFacilityStaff($facility);

        if (! $facilityStaff) {
            return $this->unauthorized();
        }

        $upcoming = $this->baseQuery($facility, $facilityStaff)
            ->whereIn('status', AppointmentStatus::activeStatuses())
            ->where('start_at', '>=', now())
            ->where('start_at', '<=', now()->addHours(24))
            ->with(['patient.user', 'facilityStaff.staff.user'])
            ->latest()
            ->take(10)
            ->get();

        $overdue = $this->baseQuery($facility, $facilityStaff)
            ->where('start_at', '<', now())
            ->whereNotIn('status', AppointmentStatus::finished())
            ->count();

        return $this->respond([
            'upcoming_appointments' => $upcoming,
            'overdue_appointments' => $overdue,
        ]);
    }

    public function analytics(Facility $facility): JsonResponse
    {
        $facilityStaff = $this->resolveFacilityStaff($facility);

        if (! $facilityStaff) {
            return $this->unauthorized();
        }

        $staffId = $facilityStaff->is_owner ? null : $facilityStaff->id;

        return $this->respond(
            $this->dashboardService->getCharts($facility, $staffId)
        );
    }

    public function liveAppointments(Facility $facility): JsonResponse
    {
        $facilityStaff = $this->resolveFacilityStaff($facility);

        if (! $facilityStaff) {
            return $this->unauthorized();
        }

        $appointments = $this->baseQuery($facility, $facilityStaff)
            ->whereDate('start_at', today())
            ->whereIn('status', [AppointmentStatus::CHECKED_IN, AppointmentStatus::IN_PROGRESS])
            ->with(['patient.user', 'facilityStaff.staff.user'])
            ->latest()
            ->get();

        return response()->json(['data' => $appointments]);
    }

    public function doctorsPerformance(Facility $facility): JsonResponse
    {
        $facilityStaff = $this->resolveFacilityStaff($facility);

        if (! $facilityStaff) {
            return $this->unauthorized();
        }

        if (! $facilityStaff->is_owner) {
            $staffIds = [$facilityStaff->staff_id];
        } else {
            $staffIds = FacilityStaff::where('facility_id', $facility->id)
                ->whereNull('ended_at')
                ->whereHas('staff.profession', fn ($q) => $q->where('slug', 'doctor'))
                ->pluck('staff_id');
        }

        $performance = Staff::whereIn('id', $staffIds)
            ->with('user:id,uuid,name')
            ->withCount(['appointmentsAsDoctor' => fn ($q) => $q->whereHas('facilityStaff', fn ($fs) => $fs->where('facility_id', $facility->id))])
            ->get()
            ->map(fn ($s) => [
                'uuid' => $s->uuid,
                'name' => $s->user?->name,
                'appointment_count' => $s->appointments_as_doctor_count,
            ]);

        return response()->json(['data' => $performance]);
    }

    public function patients(Facility $facility): JsonResponse
    {
        $facilityStaff = $this->resolveFacilityStaff($facility);

        if (! $facilityStaff) {
            return $this->unauthorized();
        }

        $patientIds = $this->baseQuery($facility, $facilityStaff)
            ->distinct('patient_id')
            ->pluck('patient_id');

        $patients = Patient::whereIn('id', $patientIds)
            ->with('user:id,uuid,name')
            ->latest()
            ->paginate(15);

        return response()->json($patients);
    }

    public function schedules(Facility $facility): JsonResponse
    {
        $facilityStaff = $this->resolveFacilityStaff($facility);

        if (! $facilityStaff) {
            return $this->unauthorized();
        }

        $query = $facilityStaff->is_owner
            ? StaffSchedule::whereHas('facilityStaff', fn ($q) => $q->where('facility_id', $facility->id))
            : $facilityStaff->schedules();

        $schedules = $query->with(['facilityStaff.staff.user', 'facilityStaff.facility'])
            ->latest()
            ->paginate(15);

        return response()->json(['data' => $schedules]);
    }

    private function baseQuery(Facility $facility, FacilityStaff $staff)
    {
        return $staff->is_owner
            ? $facility->appointments()
            : $staff->appointments();
    }

    private function resolveFacilityStaff(Facility $facility): ?FacilityStaff
    {
        return auth()->user()?->staff?->facilityStaff()
            ->where('facility_id', $facility->id)
            ->first();
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json(['message' => 'Unauthorized facility access.'], 403);
    }
}
