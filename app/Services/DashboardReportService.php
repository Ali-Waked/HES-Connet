<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Appointment;
use App\Models\Article;
use App\Models\Category;
use App\Models\Department;
use App\Models\Donation;
use App\Models\Facility;
use App\Models\JobPost;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Story;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardReportService
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function getOverview(array $filters): array
    {
        $query = $this->applyDateFilterToQuery(DB::table('appointments'), $filters);

        return [
            'total_users' => $this->countFiltered(new User, $filters),
            'total_facilities' => $this->countFiltered(new Facility, $filters),
            'total_departments' => $this->countFiltered(new Department, $filters),
            'total_categories' => $this->countFiltered(new Category, $filters),
            'total_articles' => $this->countFiltered(new Article, $filters),
            'total_stories' => $this->countFiltered(new Story, $filters),
            'total_job_posts' => $this->countFiltered(new JobPost, $filters),
            'total_staff' => $this->countFiltered(new Staff, $filters),
            'total_patients' => $this->countFiltered(new Patient, $filters),
            'total_donations' => $this->countFiltered(new Donation, $filters),
            'total_appointments' => (clone $query)->count(),
        ];
    }

    public function getCharts(array $filters): array
    {
        return [
            'users_growth' => $this->monthlyCountFiltered(new User, $filters),
            'facilities_growth' => $this->monthlyCountFiltered(new Facility, $filters),
            'articles_per_month' => $this->monthlyCountFiltered(new Article, $filters),
            'stories_per_month' => $this->monthlyCountFiltered(new Story, $filters),
            'appointments_per_month' => $this->monthlyCountFiltered(new Appointment, $filters),
            'job_posts_per_month' => $this->monthlyCountFiltered(new JobPost, $filters),
            'donations_per_month' => $this->monthlyCountFiltered(new Donation, $filters),
            'top_categories' => $this->topCategoriesFiltered($filters),
            'top_departments' => $this->topDepartmentsFiltered($filters),
            'top_facilities' => $this->topFacilitiesFiltered($filters),
        ];
    }

    public function getTables(array $filters): array
    {
        return [
            'top_facilities' => $this->topFacilitiesTable($filters),
            'top_departments' => $this->topDepartmentsTable($filters),
            'top_categories' => $this->topCategoriesTable($filters),
            'top_doctors' => $this->topDoctorsTable($filters),
            'latest_donations' => $this->latestDonations($filters),
            'latest_articles' => $this->latestArticles($filters),
            'latest_stories' => $this->latestStories($filters),
            'latest_job_posts' => $this->latestJobPosts($filters),
            'most_active_users' => $this->mostActiveUsers($filters),
        ];
    }

    public function getExportData(array $filters): array
    {
        $overview = $this->getOverview($filters);
        $tables = $this->getTables($filters);

        return [
            'generated_at' => now()->toIso8601String(),
            'filters_applied' => $filters,
            'overview' => $overview,
            'charts' => $this->getCharts($filters),
            'tables' => $tables,
        ];
    }

    private function countFiltered($model, array $filters): int
    {
        $query = $model->query();
        $query = $this->applyDateFilterToModel($query, $filters);
        $query = $this->applyStatusFilter($query, $filters);

        return $query->count();
    }

    private function monthlyCountFiltered($model, array $filters): Collection
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

        $query = $model->where('created_at', '>=', $now->copy()->subMonths(12)->startOfMonth());
        $query = $this->applyDateFilterToModel($query, $filters);
        $query = $this->applyStatusFilter($query, $filters);

        $raw = $query
            ->selectRaw("{$dateFormat} as month, COUNT(*) as total")
            ->groupBy(DB::raw($dateFormat))
            ->orderBy('month')
            ->pluck('total', 'month');

        return $months->map(fn (string $m) => [
            'label' => Carbon::createFromFormat('Y-m', $m)->format('M'),
            'value' => (int) ($raw[$m] ?? 0),
        ]);
    }

    private function topCategoriesFiltered(array $filters): Collection
    {
        return Category::withCount(['articles' => function ($q) use ($filters) {
            $q = $this->applyDateFilterToModel($q, $filters);
            $q = $this->applyStatusFilter($q, $filters, 'status');
        }])
            ->orderByDesc('articles_count')
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'uuid' => $c->uuid,
                'name' => $c->getTranslations('name'),
                'count' => $c->articles_count,
            ]);
    }

    private function topDepartmentsFiltered(array $filters): Collection
    {
        return Department::withCount(['facilityStaff' => function ($q) use ($filters) {
            $q = $this->applyDateFilterToModel($q, $filters);
        }])
            ->orderByDesc('facility_staff_count')
            ->limit(10)
            ->get()
            ->map(fn ($d) => [
                'uuid' => $d->uuid,
                'name' => $d->getTranslations('name'),
                'staff_count' => $d->facility_staff_count,
            ]);
    }

    private function topFacilitiesFiltered(array $filters): Collection
    {
        return Facility::withCount(['facilityStaff' => function ($q) use ($filters) {
            $q = $this->applyDateFilterToModel($q, $filters);
        }])
            ->orderByDesc('facility_staff_count')
            ->limit(10)
            ->get()
            ->map(fn ($f) => [
                'uuid' => $f->uuid,
                'name' => $f->getTranslations('name'),
                'type' => $f->facility_type,
                'staff_count' => $f->facility_staff_count,
            ]);
    }

    private function topFacilitiesTable(array $filters): Collection
    {
        $driver = DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite'
            ? "strftime('%Y-%m', appointments.created_at)"
            : "DATE_FORMAT(appointments.created_at, '%Y-%m')";

        $query = DB::table('appointments')
            ->join('facility_staff', 'appointments.facility_staff_id', '=', 'facility_staff.id')
            ->join('facilities', 'facility_staff.facility_id', '=', 'facilities.id')
            ->selectRaw('
                facilities.id,
                facilities.uuid,
                facilities.name,
                COUNT(*) as appointment_count,
                SUM(CASE WHEN appointments.status = "completed" THEN 1 ELSE 0 END) as completed_count
            ')
            ->groupBy('facilities.id', 'facilities.uuid', 'facilities.name')
            ->orderByDesc('appointment_count')
            ->limit(10);

        $query = $this->applyDateFilterToQuery($query, $filters, 'appointments.created_at');

        return $query->get()->map(fn ($row) => [
            'uuid' => $row->uuid,
            'name' => json_decode($row->name, true) ?? $row->name,
            'appointment_count' => (int) $row->appointment_count,
            'completed_count' => (int) $row->completed_count,
            'completion_rate' => $row->appointment_count > 0
                ? round(($row->completed_count / $row->appointment_count) * 100, 1)
                : 0,
        ]);
    }

    private function topDepartmentsTable(array $filters): Collection
    {
        $query = Department::withCount(['facilityStaff' => function ($q) use ($filters) {
            $q = $this->applyDateFilterToModel($q, $filters);
        }])
            ->with('facility:id,uuid,name')
            ->orderByDesc('facility_staff_count')
            ->limit(10);

        if (! empty($filters['facility'])) {
            $query->whereHas('facility', fn ($q) => $q->where('uuid', $filters['facility']));
        }

        return $query->get()->map(fn ($d) => [
            'uuid' => $d->uuid,
            'name' => $d->getTranslations('name'),
            'facility_name' => $d->facility?->getTranslations('name'),
            'staff_count' => $d->facility_staff_count,
        ]);
    }

    private function topCategoriesTable(array $filters): Collection
    {
        $query = Category::withCount(['articles' => function ($q) use ($filters) {
            $q = $this->applyDateFilterToModel($q, $filters);
            $q = $this->applyStatusFilter($q, $filters, 'status');
        }])
            ->orderByDesc('articles_count')
            ->limit(10);

        if (! empty($filters['category'])) {
            $query->where('uuid', $filters['category']);
        }

        return $query->get()->map(fn ($c) => [
            'uuid' => $c->uuid,
            'name' => $c->getTranslations('name'),
            'articles_count' => $c->articles_count,
        ]);
    }

    private function topDoctorsTable(array $filters): Collection
    {
        $query = DB::table('appointments')
            ->join('facility_staff', 'appointments.facility_staff_id', '=', 'facility_staff.id')
            ->join('staff', 'facility_staff.staff_id', '=', 'staff.id')
            ->join('users', 'staff.user_id', '=', 'users.id')
            ->join('facilities', 'facility_staff.facility_id', '=', 'facilities.id')
            ->selectRaw('
                staff.id,
                users.uuid,
                users.name,
                COUNT(*) as appointment_count,
                SUM(CASE WHEN appointments.status = "completed" THEN 1 ELSE 0 END) as completed_count
            ')
            ->groupBy('staff.id', 'users.uuid', 'users.name')
            ->orderByDesc('appointment_count')
            ->limit(10);

        $query = $this->applyDateFilterToQuery($query, $filters, 'appointments.created_at');

        if (! empty($filters['facility'])) {
            $query->where('facilities.uuid', $filters['facility']);
        }

        if (! empty($filters['department'])) {
            $query->where('staff.department_id', function ($q) use ($filters) {
                $q->select('id')->from('departments')->where('uuid', $filters['department']);
            });
        }

        return $query->get()->map(fn ($row) => [
            'uuid' => $row->uuid,
            'name' => json_decode($row->name, true) ?? $row->name,
            'appointment_count' => (int) $row->appointment_count,
            'completed_count' => (int) $row->completed_count,
            'completion_rate' => $row->appointment_count > 0
                ? round(($row->completed_count / $row->appointment_count) * 100, 1)
                : 0,
        ]);
    }

    private function latestDonations(array $filters): Collection
    {
        $query = Donation::with('donor:id,uuid,name', 'story:id,uuid')
            ->latest();

        $query = $this->applyDateFilterToModel($query, $filters);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->limit(10)->get()->map(fn ($d) => [
            'uuid' => $d->uuid,
            'amount' => $d->amount,
            'currency' => $d->currency ?? 'USD',
            'donor_name' => $d->donor?->getTranslations('name'),
            'story_uuid' => $d->story?->uuid,
            'status' => $d->status,
            'created_at' => $d->created_at,
        ]);
    }

    private function latestArticles(array $filters): Collection
    {
        $query = Article::with('author:id,uuid,name', 'category:id,uuid,name')
            ->latest();

        $query = $this->applyDateFilterToModel($query, $filters);
        $query = $this->applyStatusFilter($query, $filters, 'status');

        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('uuid', $filters['category']));
        }

        return $query->limit(10)->get()->map(fn ($a) => [
            'uuid' => $a->uuid,
            'title' => $a->getTranslations('title'),
            'author_name' => $a->author?->getTranslations('name'),
            'category_name' => $a->category?->getTranslations('name'),
            'status' => $a->status,
            'created_at' => $a->created_at,
        ]);
    }

    private function latestStories(array $filters): Collection
    {
        $query = Story::with('patient.user:id,uuid,name', 'category:id,uuid,name')
            ->latest();

        $query = $this->applyDateFilterToModel($query, $filters);
        $query = $this->applyStatusFilter($query, $filters, 'status');

        return $query->limit(10)->get()->map(fn ($s) => [
            'uuid' => $s->uuid,
            'title' => $s->getTranslations('title'),
            'patient_name' => $s->patient?->user?->getTranslations('name'),
            'category_name' => $s->category?->getTranslations('name'),
            'status' => $s->status,
            'created_at' => $s->created_at,
        ]);
    }

    private function latestJobPosts(array $filters): Collection
    {
        $query = JobPost::with('facility:id,uuid,name', 'user:id,uuid,name')
            ->latest();

        $query = $this->applyDateFilterToModel($query, $filters);
        $query = $this->applyStatusFilter($query, $filters, 'status');

        if (! empty($filters['facility'])) {
            $query->whereHas('facility', fn ($q) => $q->where('uuid', $filters['facility']));
        }

        return $query->limit(10)->get()->map(fn ($j) => [
            'uuid' => $j->uuid,
            'title' => $j->getTranslations('title'),
            'facility_name' => $j->facility?->getTranslations('name'),
            'posted_by' => $j->user?->getTranslations('name'),
            'status' => $j->status,
            'created_at' => $j->created_at,
        ]);
    }

    private function mostActiveUsers(array $filters): Collection
    {
        $query = DB::table('appointments')
            ->join('patients', 'appointments.patient_id', '=', 'patients.id')
            ->join('users', 'patients.user_id', '=', 'users.id')
            ->selectRaw('
                users.uuid,
                users.name,
                COUNT(*) as appointment_count,
                MAX(appointments.created_at) as last_activity
            ')
            ->groupBy('users.uuid', 'users.name')
            ->orderByDesc('appointment_count')
            ->limit(10);

        $query = $this->applyDateFilterToQuery($query, $filters, 'appointments.created_at');

        return $query->get()->map(fn ($row) => [
            'uuid' => $row->uuid,
            'name' => json_decode($row->name, true) ?? $row->name,
            'appointment_count' => (int) $row->appointment_count,
            'last_activity' => $row->last_activity,
        ]);
    }

    private function applyDateFilterToModel($query, array $filters): mixed
    {
        if (! empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query;
    }

    private function applyDateFilterToQuery($query, array $filters, string $column = 'created_at'): mixed
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
        if (! empty($filters['status'])) {
            $query->where($column, $filters['status']);
        }

        return $query;
    }
}
