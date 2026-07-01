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
use App\Models\Story;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FacilityReportService
{
    public function __construct(
        private readonly NewFacilityDashboardService $dashboardService,
    ) {}

    public function getOverview(Facility $facility, array $filters): array
    {
        $facilityId = $facility->id;

        $appointmentQuery = Appointment::whereHas('facilityStaff', fn ($q) => $q->where('facility_id', $facilityId));
        $appointmentQuery = $this->applyDateFilter($appointmentQuery, $filters);
        $appointmentQuery = $this->applyStatusFilter($appointmentQuery, $filters, 'status');

        return [
            'total_staff' => $this->countWithFilter(FacilityStaff::where('facility_id', $facilityId)->whereNull('ended_at'), $filters),
            'total_doctors' => $this->countWithFilter(
                FacilityStaff::where('facility_id', $facilityId)->whereNull('ended_at')
                    ->whereHas('role', fn ($q) => $q->where('slug', 'doctor')),
                $filters
            ),
            'total_departments' => FacilityStaff::where('facility_id', $facilityId)
                ->whereNotNull('department_id')->distinct('department_id')->count('department_id'),
            'total_patients' => (clone $appointmentQuery)->distinct('patient_id')->count('patient_id'),
            'total_appointments' => (clone $appointmentQuery)->count(),
            'total_articles' => $this->countWithFilter(Article::where('facility_id', $facilityId), $filters),
            'total_job_posts' => $this->countWithFilter(JobPost::where('facility_id', $facilityId), $filters),
        ];
    }

    public function getCharts(Facility $facility, array $filters): array
    {
        $facilityId = $facility->id;

        return [
            'appointments_per_month' => $this->appointmentsPerMonthFiltered($facilityId, $filters),
            'patients_growth' => $this->patientsPerMonthFiltered($facilityId, $filters),
            'articles_published' => $this->articlesPerMonthFiltered($facilityId, $filters),
            'appointment_status' => $this->appointmentStatusDistribution($facilityId, $filters),
            'top_doctors' => $this->topDoctorsFiltered($facilityId, $filters),
            'top_departments' => $this->topDepartmentsForFacility($facilityId),
        ];
    }

    public function getTables(Facility $facility, array $filters): array
    {
        $facilityId = $facility->id;

        return [
            'top_doctors' => $this->topDoctorsTable($facilityId, $filters),
            'top_departments' => $this->topDepartmentsTable($facilityId),
            'latest_patients' => $this->latestPatients($facilityId, $filters),
            'latest_appointments' => $this->latestAppointments($facilityId, $filters),
            'latest_stories' => $this->latestStories($facilityId),
            'latest_articles' => $this->latestArticles($facilityId),
            'most_active_doctors' => $this->mostActiveDoctors($facilityId, $filters),
            'most_booked_departments' => $this->mostBookedDepartments($facilityId, $filters),
        ];
    }

    public function getExportData(Facility $facility, array $filters): array
    {
        return [
            'generated_at' => now()->toIso8601String(),
            'filters_applied' => $filters,
            'facility' => [
                'uuid' => $facility->uuid,
                'name' => $facility->getTranslations('name'),
            ],
            'overview' => $this->getOverview($facility, $filters),
            'charts' => $this->getCharts($facility, $filters),
            'tables' => $this->getTables($facility, $filters),
        ];
    }

    private function countWithFilter($query, array $filters): int
    {
        return $this->applyDateFilter($query, $filters)->count();
    }

    private function applyDateFilter($query, array $filters, string $column = 'created_at'): mixed
    {
        if (! empty($filters['from_date'])) {
            $query->whereDate($column, '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->whereDate($column, '<=', $filters['to_date']);
        }

        return $query;
    }

    private function applyStatusFilter($query, array $filters, string $column = 'status'): mixed
    {
        if (! empty($filters['appointment_status'])) {
            $query->where($column, $filters['appointment_status']);
        }

        return $query;
    }

    private function appointmentsPerMonthFiltered(int $facilityId, array $filters): Collection
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

        $query = DB::table('appointments')
            ->join('facility_staff', 'appointments.facility_staff_id', '=', 'facility_staff.id')
            ->where('facility_staff.facility_id', $facilityId)
            ->where('appointments.created_at', '>=', $now->copy()->subMonths(12)->startOfMonth());

        $query = $this->applyDateFilter($query, $filters, 'appointments.created_at');
        $query = $this->applyStatusFilter($query, $filters, 'appointments.status');

        $raw = (clone $query)
            ->selectRaw("{$dateFormat} as month, COUNT(*) as total")
            ->groupBy(DB::raw($dateFormat))
            ->orderBy('month')
            ->pluck('total', 'month');

        return $months->map(fn (string $m) => [
            'label' => Carbon::createFromFormat('Y-m', $m)->format('M'),
            'value' => (int) ($raw[$m] ?? 0),
        ]);
    }

    private function patientsPerMonthFiltered(int $facilityId, array $filters): Collection
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

        $query = DB::table('appointments')
            ->join('facility_staff', 'appointments.facility_staff_id', '=', 'facility_staff.id')
            ->where('facility_staff.facility_id', $facilityId)
            ->where('appointments.created_at', '>=', $now->copy()->subMonths(12)->startOfMonth());

        $query = $this->applyDateFilter($query, $filters, 'appointments.created_at');

        $raw = (clone $query)
            ->selectRaw("{$dateFormat} as month, COUNT(DISTINCT patient_id) as total")
            ->groupBy(DB::raw($dateFormat))
            ->orderBy('month')
            ->pluck('total', 'month');

        return $months->map(fn (string $m) => [
            'label' => Carbon::createFromFormat('Y-m', $m)->format('M'),
            'value' => (int) ($raw[$m] ?? 0),
        ]);
    }

    private function articlesPerMonthFiltered(int $facilityId, array $filters): Collection
    {
        $now = now();
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $months->push($date->format('Y-m'));
        }

        $driver = DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $query = Article::where('facility_id', $facilityId)
            ->where('created_at', '>=', $now->copy()->subMonths(12)->startOfMonth());

        $query = $this->applyDateFilter($query, $filters);

        $raw = (clone $query)
            ->selectRaw("{$dateFormat} as month, COUNT(*) as total")
            ->groupBy(DB::raw($dateFormat))
            ->orderBy('month')
            ->pluck('total', 'month');

        return $months->map(fn (string $m) => [
            'label' => Carbon::createFromFormat('Y-m', $m)->format('M'),
            'value' => (int) ($raw[$m] ?? 0),
        ]);
    }

    private function appointmentStatusDistribution(int $facilityId, array $filters): Collection
    {
        $query = DB::table('appointments')
            ->join('facility_staff', 'appointments.facility_staff_id', '=', 'facility_staff.id')
            ->where('facility_staff.facility_id', $facilityId);

        $query = $this->applyDateFilter($query, $filters, 'appointments.created_at');

        return (clone $query)
            ->selectRaw('appointments.status, COUNT(*) as count')
            ->groupBy('appointments.status')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status,
                'count' => (int) $row->count,
            ]);
    }

    private function topDoctorsFiltered(int $facilityId, array $filters): Collection
    {
        $staffIds = FacilityStaff::where('facility_id', $facilityId)
            ->whereNull('ended_at')
            ->whereHas('role', fn ($q) => $q->where('slug', 'doctor'))
            ->pluck('staff_id');

        return Staff::whereIn('id', $staffIds)
            ->with('user:id,uuid,name')
            ->withCount(['appointmentsAsDoctor' => fn ($q) => $q
                ->whereHas('facilityStaff', fn ($fs) => $fs->where('facility_id', $facilityId))
                ->when($filters['from_date'] ?? null, fn ($q, $v) => $q->whereDate('appointments.created_at', '>=', $v))
                ->when($filters['to_date'] ?? null, fn ($q, $v) => $q->whereDate('appointments.created_at', '<=', $v))
                ->when($filters['appointment_status'] ?? null, fn ($q, $v) => $q->where('appointments.status', $v)),
            ])
            ->orderByDesc('appointments_as_doctor_count')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'uuid' => $s->uuid,
                'name' => $s->user?->getTranslations('name'),
                'appointment_count' => $s->appointments_as_doctor_count,
            ]);
    }

    private function topDepartmentsForFacility(int $facilityId): Collection
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

    private function topDoctorsTable(int $facilityId, array $filters): Collection
    {
        return $this->topDoctorsFiltered($facilityId, $filters)->map(fn ($d) => [
            'uuid' => $d['uuid'],
            'name' => $d['name'],
            'appointment_count' => $d['appointment_count'],
        ]);
    }

    private function topDepartmentsTable(int $facilityId): Collection
    {
        return $this->topDepartmentsForFacility($facilityId);
    }

    private function latestPatients(int $facilityId, array $filters): Collection
    {
        $patientIds = Appointment::whereHas('facilityStaff', fn ($q) => $q->where('facility_id', $facilityId))
            ->when($filters['from_date'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to_date'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->distinct('patient_id')
            ->pluck('patient_id');

        return Patient::whereIn('id', $patientIds)
            ->with('user:id,uuid,name')
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'uuid' => $p->uuid,
                'name' => $p->user?->getTranslations('name'),
                'email' => $p->user?->email,
                'created_at' => $p->created_at,
            ]);
    }

    private function latestAppointments(int $facilityId, array $filters): Collection
    {
        $query = Appointment::whereHas('facilityStaff', fn ($q) => $q->where('facility_id', $facilityId))
            ->with('facilityStaff.staff.user:id,uuid,name', 'patient.user:id,uuid,name');

        $query = $this->applyDateFilter($query, $filters);
        $query = $this->applyStatusFilter($query, $filters, 'status');

        return $query->latest()->limit(10)->get()->map(fn ($a) => [
            'uuid' => $a->uuid,
            'patient_name' => $a->patient?->user?->getTranslations('name'),
            'doctor_name' => $a->facilityStaff?->staff?->user?->getTranslations('name'),
            'status' => $a->status?->value,
            'start_at' => $a->start_at,
            'created_at' => $a->created_at,
        ]);
    }

    private function latestStories(int $facilityId): Collection
    {
        return Story::whereHas('patient.appointments.facilityStaff', fn ($q) => $q->where('facility_id', $facilityId))
            ->with('patient.user:id,uuid,name')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'uuid' => $s->uuid,
                'title' => $s->getTranslations('title'),
                'patient_name' => $s->patient?->user?->getTranslations('name'),
                'status' => $s->status,
                'created_at' => $s->created_at,
            ]);
    }

    private function latestArticles(int $facilityId): Collection
    {
        return Article::where('facility_id', $facilityId)
            ->with('author:id,uuid,name')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'uuid' => $a->uuid,
                'title' => $a->getTranslations('title'),
                'author_name' => $a->author?->getTranslations('name'),
                'status' => $a->status,
                'created_at' => $a->created_at,
            ]);
    }

    private function mostActiveDoctors(int $facilityId, array $filters): Collection
    {
        $staffIds = FacilityStaff::where('facility_id', $facilityId)
            ->whereNull('ended_at')
            ->whereHas('role', fn ($q) => $q->where('slug', 'doctor'))
            ->pluck('staff_id');

        $baseQuery = DB::table('appointments')
            ->join('facility_staff', 'appointments.facility_staff_id', '=', 'facility_staff.id')
            ->join('staff', 'facility_staff.staff_id', '=', 'staff.id')
            ->join('users', 'staff.user_id', '=', 'users.id')
            ->where('facility_staff.facility_id', $facilityId);

        $baseQuery = $this->applyDateFilter($baseQuery, $filters, 'appointments.created_at');
        $baseQuery = $this->applyStatusFilter($baseQuery, $filters, 'appointments.status');

        if (! empty($filters['doctor'])) {
            $baseQuery->where('users.uuid', $filters['doctor']);
        }

        return (clone $baseQuery)
            ->selectRaw('
                users.uuid,
                users.name,
                COUNT(*) as appointment_count,
                MAX(appointments.created_at) as last_activity
            ')
            ->groupBy('users.uuid', 'users.name')
            ->orderByDesc('appointment_count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'uuid' => $row->uuid,
                'name' => json_decode($row->name, true) ?? $row->name,
                'appointment_count' => (int) $row->appointment_count,
                'last_activity' => $row->last_activity,
            ]);
    }

    private function mostBookedDepartments(int $facilityId, array $filters): Collection
    {
        $query = DB::table('appointments')
            ->join('facility_staff', 'appointments.facility_staff_id', '=', 'facility_staff.id')
            ->join('departments', 'facility_staff.department_id', '=', 'departments.id')
            ->where('facility_staff.facility_id', $facilityId);

        $query = $this->applyDateFilter($query, $filters, 'appointments.created_at');
        $query = $this->applyStatusFilter($query, $filters, 'appointments.status');

        return (clone $query)
            ->selectRaw('
                departments.uuid,
                departments.name,
                COUNT(*) as appointment_count
            ')
            ->groupBy('departments.uuid', 'departments.name')
            ->orderByDesc('appointment_count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'uuid' => $row->uuid,
                'name' => json_decode($row->name, true) ?? $row->name,
                'appointment_count' => (int) $row->appointment_count,
            ]);
    }
}
