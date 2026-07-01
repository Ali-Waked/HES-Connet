<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Appointment;
use App\Models\Article;
use App\Models\Category;
use App\Models\Department;
use App\Models\Donation;
use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\JobPost;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Story;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NewFacilityDashboardService
{
    use MonthlyCountTrait;

    public function getCards(Facility $facility): array
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
            'total_patients' => Appointment::whereHas('facilityStaff', fn ($q) => $q->where('facility_id', $facilityId))
                ->distinct('patient_id')
                ->count('patient_id'),
            'total_appointments' => Appointment::whereHas('facilityStaff', fn ($q) => $q->where('facility_id', $facilityId))->count(),
            'total_articles' => Article::where('facility_id', $facilityId)->count(),
            'total_stories' => Story::whereHas('patient', fn ($q) => $q->whereHas('appointments.facilityStaff', fn ($fs) => $fs->where('facility_id', $facilityId)))->count(),
            'total_job_posts' => JobPost::where('facility_id', $facilityId)->count(),
            'total_donations' => Donation::whereHas('story.patient', fn ($q) => $q->whereHas('appointments.facilityStaff', fn ($fs) => $fs->where('facility_id', $facilityId)))->count(),
            'total_categories' => Category::whereHas('articles', fn ($q) => $q->where('facility_id', $facilityId))->count(),
        ];
    }

    public function getGrowthPercentages(Facility $facility): array
    {
        $facilityId = $facility->id;

        return [
            'staff_growth' => $this->growthPercentageForFacilityStaff($facilityId),
            'doctors_growth' => $this->growthPercentageForDoctors($facilityId),
            'departments_growth' => $this->growthPercentageForDepartments($facilityId),
            'patients_growth' => $this->growthPercentageForPatients($facilityId),
            'appointments_growth' => $this->growthPercentageForAppointments($facilityId),
            'articles_growth' => $this->growthPercentage(new Article, $facilityId, 'facility_id'),
            'job_posts_growth' => $this->growthPercentage(new JobPost, $facilityId, 'facility_id'),
        ];
    }

    public function getRecentData(Facility $facility): array
    {
        $facilityId = $facility->id;

        return [
            'staff' => $this->recentStaff($facilityId),
            'doctors' => $this->recentDoctors($facilityId),
            'patients' => $this->recentPatients($facilityId),
            'articles' => $this->recentArticles($facilityId),
            'stories' => $this->recentStories($facilityId),
            'job_posts' => $this->recentJobPosts($facilityId),
            'appointments' => $this->recentAppointments($facilityId),
            'departments' => $this->recentDepartments($facilityId),
        ];
    }

    public function getCharts(Facility $facility): array
    {
        $facilityId = $facility->id;

        $facilityStaffIds = FacilityStaff::where('facility_id', $facilityId)->pluck('id');
        $staffIds = FacilityStaff::where('facility_id', $facilityId)->pluck('staff_id');

        return [
            'appointments_per_month' => $this->appointmentsPerMonth($facilityId),
            'patients_growth' => $this->patientsPerMonth($facilityId),
            'doctors_growth' => $this->monthlyCount(
                FacilityStaff::whereNull('ended_at')->whereHas('role', fn ($q) => $q->where('slug', 'doctor')),
                $facilityId,
                'facility_id'
            ),
            'stories_published' => $this->storiesPerMonth($facilityId),
            'articles_published' => $this->monthlyCount(new Article, $facilityId, 'facility_id'),
            'top_departments' => $this->topDepartments($facilityId),
            'top_doctors' => $this->topDoctors($facilityId),
            'top_symptoms' => $this->topSymptoms($facilityId),
            'appointment_status' => $this->appointmentStatusDistribution($facilityId),
        ];
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

    private function growthPercentageForPatients(int $facilityId): float
    {
        $now = now();
        $currentPeriod = (clone $now)->startOfMonth();
        $previousPeriod = (clone $now)->subMonth()->startOfMonth();

        $countForPeriod = function ($start, $end) use ($facilityId) {
            return Appointment::whereHas('facilityStaff', fn ($q) => $q->where('facility_id', $facilityId))
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

    private function growthPercentageForAppointments(int $facilityId): float
    {
        $now = now();
        $currentPeriod = (clone $now)->startOfMonth();
        $previousPeriod = (clone $now)->subMonth()->startOfMonth();

        $base = fn ($dateQuery) => Appointment::whereHas('facilityStaff', fn ($q) => $q->where('facility_id', $facilityId))
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
            ->with('user:id,uuid,name')
            ->latest('id')
            ->take(5)
            ->get()
            ->map(fn ($s) => [
                'uuid' => $s->uuid,
                'name' => $s->user?->getTranslations('name'),
                'specialization' => $s->getTranslations('specialization'),
                'created_at' => $s->created_at,
            ]);
    }

    private function recentDoctors(int $facilityId): Collection
    {
        return FacilityStaff::where('facility_id', $facilityId)
            ->whereNull('ended_at')
            ->whereHas('role', fn ($q) => $q->where('slug', 'doctor'))
            ->with('staff.user:id,uuid,name')
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(fn ($fs) => [
                'uuid' => $fs->staff->uuid,
                'name' => $fs->staff->user?->getTranslations('name'),
                'specialization' => $fs->staff->getTranslations('specialization'),
                'created_at' => $fs->created_at,
            ]);
    }

    private function recentPatients(int $facilityId): Collection
    {
        $patientIds = Appointment::whereHas('facilityStaff', fn ($q) => $q->where('facility_id', $facilityId))
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
        return Article::where('facility_id', $facilityId)
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

    private function recentStories(int $facilityId): Collection
    {
        return Story::whereHas('patient.appointments.facilityStaff', fn ($q) => $q->where('facility_id', $facilityId))
            ->with('patient.user:id,uuid,name')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($s) => [
                'uuid' => $s->uuid,
                'title' => $s->getTranslations('title'),
                'status' => $s->status,
                'created_at' => $s->created_at,
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

    private function recentAppointments(int $facilityId): Collection
    {
        return Appointment::whereHas('facilityStaff', fn ($q) => $q->where('facility_id', $facilityId))
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

    private function appointmentsPerMonth(int $facilityId): Collection
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

    private function patientsPerMonth(int $facilityId): Collection
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

    private function storiesPerMonth(int $facilityId): Collection
    {
        $now = now();
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $months->push($date->format('Y-m'));
        }

        $driver = DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite'
            ? "strftime('%Y-%m', stories.created_at)"
            : "DATE_FORMAT(stories.created_at, '%Y-%m')";

        $from = $now->copy()->subMonths(12)->startOfMonth();

        $raw = DB::table('stories')
            ->join('patients', 'stories.patient_id', '=', 'patients.id')
            ->join('appointments', 'patients.id', '=', 'appointments.patient_id')
            ->join('facility_staff', 'appointments.facility_staff_id', '=', 'facility_staff.id')
            ->where('facility_staff.facility_id', $facilityId)
            ->where('stories.created_at', '>=', $from)
            ->selectRaw("{$dateFormat} as month, COUNT(DISTINCT stories.id) as total")
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

    private function topDoctors(int $facilityId): Collection
    {
        $staffIds = FacilityStaff::where('facility_id', $facilityId)
            ->whereNull('ended_at')
            ->whereHas('role', fn ($q) => $q->where('slug', 'doctor'))
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
        return DB::table('facility_staff_symptom')
            ->join('facility_staff', 'facility_staff_symptom.facility_staff_id', '=', 'facility_staff.id')
            ->join('symptoms', 'facility_staff_symptom.symptom_id', '=', 'symptoms.id')
            ->where('facility_staff.facility_id', $facilityId)
            ->selectRaw('symptoms.id, symptoms.uuid, symptoms.name, COUNT(*) as total')
            ->groupBy('symptoms.id', 'symptoms.uuid', 'symptoms.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'uuid' => $row->uuid,
                'name' => json_decode($row->name, true) ?? $row->name,
                'count' => (int) $row->total,
            ]);
    }

    private function appointmentStatusDistribution(int $facilityId): Collection
    {
        return DB::table('appointments')
            ->join('facility_staff', 'appointments.facility_staff_id', '=', 'facility_staff.id')
            ->where('facility_staff.facility_id', $facilityId)
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
