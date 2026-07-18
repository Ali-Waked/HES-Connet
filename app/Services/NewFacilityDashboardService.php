<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Appointment;
use App\Models\Article;
use App\Models\Department;
use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\JobPost;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NewFacilityDashboardService
{
    use MonthlyCountTrait;

    public function getCards(Facility $facility, ?int $facilityStaffId = null): array
    {
        $facilityId = $facility->id;

        return [
            'total_staff' => FacilityStaff::where('facility_id', $facilityId)->whereNull('ended_at')->count(),
            'total_doctors' => FacilityStaff::where('facility_id', $facilityId)
                ->whereNull('ended_at')
                ->whereHas('role', fn ($q) => $q->where('slug', 'doctor'))
                ->count(),
            'total_departments' => FacilityStaff::where('facility_id', $facilityId)
                ->whereNotNull('department_id')->distinct('department_id')->count('department_id'),
            'total_patients' => $this->appointmentQuery($facilityId, $facilityStaffId)
                ->distinct('patient_id')
                ->count('patient_id'),
            'total_appointments' => $this->appointmentQuery($facilityId, $facilityStaffId)->count(),
            'total_articles' => Article::forFacility($facilityId)->count(),
            'total_job_posts' => JobPost::where('facility_id', $facilityId)->count(),
        ];
    }

    public function getGrowthPercentages(Facility $facility, ?int $facilityStaffId = null): array
    {
        $facilityId = $facility->id;

        return [
            'staff_growth' => $this->growthPercentageForFacilityStaff($facilityId),
            'doctors_growth' => $this->growthPercentageForDoctors($facilityId),
            'departments_growth' => $this->growthPercentageForDepartments($facilityId),
            'patients_growth' => $this->growthPercentageForPatients($facilityId, $facilityStaffId),
            'appointments_growth' => $this->growthPercentageForAppointments($facilityId, $facilityStaffId),
            'articles_growth' => $this->growthPercentage(Article::forFacility($facilityId)),
            'job_posts_growth' => $this->growthPercentage(new JobPost, $facilityId, 'facility_id'),
        ];
    }

    public function getRecentData(Facility $facility, ?int $facilityStaffId = null): array
    {
        $facilityId = $facility->id;

        return [
            'staff' => $this->recentStaff($facilityId),
            'doctors' => $this->recentDoctors($facilityId),
            'patients' => $this->recentPatients($facilityId, $facilityStaffId),
            'articles' => $this->recentArticles($facilityId),
            'job_posts' => $this->recentJobPosts($facilityId),
            'appointments' => $this->recentAppointments($facilityId, $facilityStaffId),
            'departments' => $this->recentDepartments($facilityId),
        ];
    }

    public function getCharts(Facility $facility, ?int $facilityStaffId = null): array
    {
        $facilityId = $facility->id;

        return [
            'appointments_per_month' => $this->appointmentsPerMonth($facilityId, $facilityStaffId),
            'patients_growth' => $this->patientsPerMonth($facilityId, $facilityStaffId),
            'doctors_growth' => $this->monthlyCount(
                FacilityStaff::whereNull('ended_at')->whereHas('role', fn ($q) => $q->where('slug', 'doctor')),
                $facilityId,
                'facility_id'
            ),
            'articles_published' => $this->monthlyCount(Article::forFacility($facilityId)),
            'top_departments' => $this->topDepartments($facilityId),
            'top_doctors' => $this->topDoctors($facilityId, $facilityStaffId),
            'top_symptoms' => $this->topSymptoms($facilityId),
            'appointment_status' => $this->appointmentStatusDistribution($facilityId, $facilityStaffId),
        ];
    }

    private function appointmentQuery(int $facilityId, ?int $facilityStaffId = null)
    {
        $query = Appointment::whereHas('facilityStaff', fn ($q) => $q->where('facility_id', $facilityId));

        if ($facilityStaffId !== null) {
            $query->where('facility_staff_id', $facilityStaffId);
        }

        return $query;
    }

    private function growthPercentageForFacilityStaff(int $facilityId): float
    {
        $now = now();
        $currentPeriod = (clone $now)->startOfMonth();
        $previousPeriod = (clone $now)->subMonth()->startOfMonth();

        $current = FacilityStaff::where('facility_id', $facilityId)
            ->whereNull('ended_at')
            ->where('created_at', '>=', $currentPeriod)
            ->count();

        $previous = FacilityStaff::where('facility_id', $facilityId)
            ->whereNull('ended_at')
            ->whereBetween('created_at', [$previousPeriod, $currentPeriod])
            ->count();

        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function growthPercentageForDoctors(int $facilityId): float
    {
        $now = now();
        $currentPeriod = (clone $now)->startOfMonth();
        $previousPeriod = (clone $now)->subMonth()->startOfMonth();

        $baseQuery = fn ($dateQuery) => FacilityStaff::where('facility_id', $facilityId)
            ->whereNull('ended_at')
            ->whereHas('role', fn ($q) => $q->where('slug', 'doctor'))
            ->where($dateQuery);

        $current = $baseQuery(fn ($q) => $q->where('created_at', '>=', $currentPeriod))->count();
        $previous = $baseQuery(fn ($q) => $q->whereBetween('created_at', [$previousPeriod, $currentPeriod]))->count();

        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function growthPercentageForPatients(int $facilityId, ?int $facultyStaffId = null): float
    {
        $now = now();
        $currentPeriod = (clone $now)->startOfMonth();
        $previousPeriod = (clone $now)->subMonth()->startOfMonth();

        $countForPeriod = function ($start, $end) use ($facilityId, $facultyStaffId) {
            return $this->appointmentQuery($facilityId, $facultyStaffId)
                ->whereBetween('created_at', [$start, $end])
                ->distinct('patient_id')
                ->count('patient_id');
        };

        $current = $countForPeriod($currentPeriod, $now);
        $previous = $countForPeriod($previousPeriod, $currentPeriod);

        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function growthPercentageForAppointments(int $facilityId, ?int $facultyStaffId = null): float
    {
        $now = now();
        $currentPeriod = (clone $now)->startOfMonth();
        $previousPeriod = (clone $now)->subMonth()->startOfMonth();

        $base = fn ($dateQuery) => $this->appointmentQuery($facilityId, $facultyStaffId)
            ->where($dateQuery);

        $current = $base(fn ($q) => $q->where('created_at', '>=', $currentPeriod))->count();
        $previous = $base(fn ($q) => $q->whereBetween('created_at', [$previousPeriod, $currentPeriod]))->count();

        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function growthPercentageForDepartments(int $facilityId): float
    {
        $now = now();
        $currentPeriod = (clone $now)->startOfMonth();
        $previousPeriod = (clone $now)->subMonth()->startOfMonth();

        $countForPeriod = function ($start, $end) use ($facilityId) {
            return FacilityStaff::where('facility_id', $facilityId)
                ->whereNotNull('department_id')
                ->whereHas('department', fn ($q) => $q->whereBetween('created_at', [$start, $end]))
                ->distinct('department_id')
                ->count('department_id');
        };

        $current = $countForPeriod($currentPeriod, $now);
        $previous = $countForPeriod($previousPeriod, $currentPeriod);

        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function recentStaff(int $facilityId): Collection
    {
        $fsIds = FacilityStaff::where('facility_id', $facilityId)->pluck('staff_id');

        return Staff::whereIn('id', $fsIds)
            ->with(['user:id,uuid,name', 'specialization'])
            ->latest('id')
            ->take(5)
            ->get()
            ->map(fn ($s) => [
                'uuid' => $s->uuid,
                'name' => $s->user?->getTranslations('name'),
                'specialization' => $s->specialization?->getTranslations('name'),
                'created_at' => $s->created_at,
            ]);
    }

    private function recentDoctors(int $facilityId): Collection
    {
        return FacilityStaff::where('facility_id', $facilityId)
            ->whereNull('ended_at')
            ->whereHas('role', fn ($q) => $q->where('slug', 'doctor'))
            ->with('staff.user:id,uuid,name', 'staff.specialization')
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(fn ($fs) => [
                'uuid' => $fs->staff->uuid,
                'name' => $fs->staff->user?->getTranslations('name'),
                'specialization' => $fs->staff->specialization?->getTranslations('name'),
                'created_at' => $fs->created_at,
            ]);
    }

    private function recentPatients(int $facilityId, ?int $facilityStaffId = null): Collection
    {
        $patientIds = $this->appointmentQuery($facilityId, $facilityStaffId)
            ->distinct('patient_id')
            ->pluck('patient_id');

        return Patient::whereIn('id', $patientIds)
            ->with('user:id,uuid,name')
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(fn ($p) => [
                'uuid' => $p->uuid,
                'name' => $p->user?->getTranslations('name'),
                'created_at' => $p->created_at,
            ]);
    }

    private function recentArticles(int $facilityId): Collection
    {
        return Article::forFacility($facilityId)
            ->with('author:id,uuid,name')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($a) => [
                'uuid' => $a->uuid,
                'title' => $a->getTranslations('title'),
                'status' => $a->status,
                'created_at' => $a->created_at,
            ]);
    }

    private function recentJobPosts(int $facilityId): Collection
    {
        return JobPost::where('facility_id', $facilityId)
            ->with('user:id,uuid,name')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($j) => [
                'uuid' => $j->uuid,
                'title' => $j->getTranslations('title'),
                'status' => $j->status,
                'created_at' => $j->created_at,
            ]);
    }

    private function recentAppointments(int $facilityId, ?int $facilityStaffId = null): Collection
    {
        return $this->appointmentQuery($facilityId, $facilityStaffId)
            ->with('facilityStaff.staff.user:id,uuid,name', 'patient.user:id,uuid,name')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($a) => [
                'uuid' => $a->uuid,
                'patient_name' => $a->patient?->user?->getTranslations('name'),
                'doctor_name' => $a->facilityStaff?->staff?->user?->getTranslations('name'),
                'status' => $a->status?->value,
                'start_at' => $a->start_at,
                'created_at' => $a->created_at,
            ]);
    }

    private function recentDepartments(int $facilityId): Collection
    {
        $departmentIds = FacilityStaff::where('facility_id', $facilityId)
            ->whereNotNull('department_id')
            ->pluck('department_id')
            ->unique();

        return Department::whereIn('id', $departmentIds)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($d) => [
                'uuid' => $d->uuid,
                'name' => $d->getTranslations('name'),
                'created_at' => $d->created_at,
            ]);
    }

    private function appointmentsPerMonth(int $facilityId, ?int $facilityStaffId = null): Collection
    {
        $now = now();
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $months->push($date->format('Y-m'));
        }

        $driver = DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite'
            ? "strftime('%Y-%m', appointments.created_at)"
            : "DATE_FORMAT(appointments.created_at, '%Y-%m')";

        $from = $now->copy()->subMonths(12)->startOfMonth();

        $raw = DB::table('appointments')
            ->join('facility_staff', 'appointments.facility_staff_id', '=', 'facility_staff.id')
            ->where('facility_staff.facility_id', $facilityId)
            ->when($facilityStaffId !== null, fn ($q) => $q->where('appointments.facility_staff_id', $facilityStaffId))
            ->where('appointments.created_at', '>=', $from)
            ->selectRaw("{$dateFormat} as month, COUNT(*) as total")
            ->groupBy(DB::raw($dateFormat))
            ->orderBy('month')
            ->pluck('total', 'month');

        return $months->map(fn (string $m) => [
            'label' => Carbon::createFromFormat('Y-m', $m)->format('M'),
            'value' => (int) ($raw[$m] ?? 0),
        ]);
    }

    private function patientsPerMonth(int $facilityId, ?int $facilityStaffId = null): Collection
    {
        $now = now();
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $months->push($date->format('Y-m'));
        }

        $driver = DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite'
            ? "strftime('%Y-%m', appointments.created_at)"
            : "DATE_FORMAT(appointments.created_at, '%Y-%m')";

        $from = $now->copy()->subMonths(12)->startOfMonth();

        $raw = DB::table('appointments')
            ->join('facility_staff', 'appointments.facility_staff_id', '=', 'facility_staff.id')
            ->where('facility_staff.facility_id', $facilityId)
            ->when($facilityStaffId !== null, fn ($q) => $q->where('appointments.facility_staff_id', $facilityStaffId))
            ->where('appointments.created_at', '>=', $from)
            ->selectRaw("{$dateFormat} as month, COUNT(DISTINCT patient_id) as total")
            ->groupBy(DB::raw($dateFormat))
            ->orderBy('month')
            ->pluck('total', 'month');

        return $months->map(fn (string $m) => [
            'label' => Carbon::createFromFormat('Y-m', $m)->format('M'),
            'value' => (int) ($raw[$m] ?? 0),
        ]);
    }

    private function topDepartments(int $facilityId): Collection
    {
        $departmentIds = FacilityStaff::where('facility_id', $facilityId)
            ->whereNotNull('department_id')
            ->pluck('department_id')
            ->unique();

        return Department::whereIn('id', $departmentIds)
            ->withCount(['facilityStaff' => fn ($q) => $q->whereNull('ended_at')])
            ->orderByDesc('facility_staff_count')
            ->limit(10)
            ->get()
            ->map(fn ($d) => [
                'uuid' => $d->uuid,
                'name' => $d->getTranslations('name'),
                'staff_count' => $d->facility_staff_count,
            ]);
    }

    private function topDoctors(int $facilityId, ?int $facilityStaffId = null): Collection
    {
        $staffIds = FacilityStaff::where('facility_id', $facilityId)
            ->whereNull('ended_at')
            ->whereHas('role', fn ($q) => $q->where('slug', 'doctor'))
            ->when($facilityStaffId !== null, fn ($q) => $q->where('id', $facilityStaffId))
            ->pluck('staff_id');

        return Staff::whereIn('id', $staffIds)
            ->with('user:id,uuid,name')
            ->withCount(['appointmentsAsDoctor' => fn ($q) => $q->whereHas('facilityStaff', fn ($fs) => $fs->where('facility_id', $facilityId))])
            ->orderByDesc('appointments_as_doctor_count')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'uuid' => $s->uuid,
                'name' => $s->user?->getTranslations('name'),
                'appointment_count' => $s->appointments_as_doctor_count,
            ]);
    }

    private function topSymptoms(int $facilityId): Collection
    {
        return DB::table('specialization_symptom')
            ->join('symptoms', 'specialization_symptom.symptom_id', '=', 'symptoms.id')
            ->join('specializations', 'specialization_symptom.specialization_id', '=', 'specializations.id')
            ->whereIn('specializations.id', function ($q) use ($facilityId) {
                $q->select('staff.specialization_id')
                    ->from('facility_staff')
                    ->join('staff', 'facility_staff.staff_id', '=', 'staff.id')
                    ->where('facility_staff.facility_id', $facilityId)
                    ->whereNull('facility_staff.ended_at')
                    ->whereNotNull('staff.specialization_id');
            })
            ->selectRaw('symptoms.id, symptoms.name, COUNT(*) as total')
            ->groupBy('symptoms.id', 'symptoms.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => json_decode($row->name, true) ?? $row->name,
                'count' => (int) $row->total,
            ]);
    }

    private function appointmentStatusDistribution(int $facilityId, ?int $facilityStaffId = null): Collection
    {
        return DB::table('appointments')
            ->join('facility_staff', 'appointments.facility_staff_id', '=', 'facility_staff.id')
            ->where('facility_staff.facility_id', $facilityId)
            ->when($facilityStaffId !== null, fn ($q) => $q->where('appointments.facility_staff_id', $facilityStaffId))
            ->selectRaw('appointments.status, COUNT(*) as count')
            ->groupBy('appointments.status')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status,
                'count' => (int) $row->count,
            ]);
    }
}
