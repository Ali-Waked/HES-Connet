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

class DashboardService
{
    public function getCards(): array
    {
        return [
            'total_users' => User::count(),
            'total_facilities' => Facility::count(),
            'total_departments' => Department::count(),
            'total_categories' => Category::count(),
            'total_articles' => Article::count(),
            'total_stories' => Story::count(),
            'total_job_posts' => JobPost::count(),
            'total_staff' => Staff::count(),
            'total_doctors' => Staff::whereHas('profession', fn ($q) => $q->where('slug', 'doctor'))->count(),
            'total_patients' => Patient::count(),
            'total_donations' => Donation::count(),
            'total_appointments' => Appointment::count(),
        ];
    }

    public function getGrowthPercentages(): array
    {
        $now = now();

        return [
            'users_growth' => $this->growthPercentage(new User, $now),
            'facilities_growth' => $this->growthPercentage(new Facility, $now),
            'departments_growth' => $this->growthPercentage(new Department, $now),
            'articles_growth' => $this->growthPercentage(new Article, $now),
            'stories_growth' => $this->growthPercentage(new Story, $now),
            'job_posts_growth' => $this->growthPercentage(new JobPost, $now),
            'staff_growth' => $this->growthPercentage(new Staff, $now),
            'patients_growth' => $this->growthPercentage(new Patient, $now),
            'donations_growth' => $this->growthPercentage(new Donation, $now),
            'appointments_growth' => $this->growthPercentage(new Appointment, $now),
        ];
    }

    private function growthPercentage($model, Carbon $now): float
    {
        $currentPeriod = (clone $now)->startOfMonth();
        $previousPeriod = (clone $now)->subMonth()->startOfMonth();

        $current = $model->where('created_at', '>=', $currentPeriod)->count();
        $previous = $model->whereBetween('created_at', [$previousPeriod, $currentPeriod])->count();

        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function getRecentActivity(): array
    {
        return [
            'users' => User::with('profile')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($u) => [
                    'uuid' => $u->uuid,
                    'name' => $u->getTranslations('name'),
                    'email' => $u->email,
                    'created_at' => $u->created_at,
                ]),

            'facilities' => Facility::latest()
                ->take(5)
                ->get()
                ->map(fn ($f) => [
                    'uuid' => $f->uuid,
                    'name' => $f->getTranslations('name'),
                    'status' => $f->status,
                    'created_at' => $f->created_at,
                ]),

            'articles' => Article::with('author:id,uuid,name')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($a) => [
                    'uuid' => $a->uuid,
                    'title' => $a->getTranslations('title'),
                    'status' => $a->status,
                    'created_at' => $a->created_at,
                    'created_by' => $a->author ? [
                        'uuid' => $a->author->uuid,
                        'name' => $a->author->getTranslations('name'),
                    ] : null,
                ]),

            'stories' => Story::with('patient.user:id,uuid,name')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($s) => [
                    'uuid' => $s->uuid,
                    'title' => $s->getTranslations('title'),
                    'status' => $s->status,
                    'created_at' => $s->created_at,
                    'created_by' => $s->patient?->user ? [
                        'uuid' => $s->patient->user->uuid,
                        'name' => $s->patient->user->getTranslations('name'),
                    ] : null,
                ]),

            'job_posts' => JobPost::with('user:id,uuid,name')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($j) => [
                    'uuid' => $j->uuid,
                    'title' => $j->getTranslations('title'),
                    'status' => $j->status,
                    'created_at' => $j->created_at,
                    'created_by' => $j->user ? [
                        'uuid' => $j->user->uuid,
                        'name' => $j->user->getTranslations('name'),
                    ] : null,
                ]),

            'departments' => Department::with('facility:id,uuid,name')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($d) => [
                    'uuid' => $d->uuid,
                    'name' => $d->getTranslations('name'),
                    'facility_name' => $d->facility?->getTranslations('name'),
                    'created_at' => $d->created_at,
                ]),

            'categories' => Category::latest()
                ->take(5)
                ->get()
                ->map(fn ($c) => [
                    'uuid' => $c->uuid,
                    'name' => $c->getTranslations('name'),
                    'created_at' => $c->created_at,
                ]),
        ];
    }

    public function getCharts(): array
    {
        return [
            'users_growth' => $this->monthlyCount(new User),
            'facilities_growth' => $this->monthlyCount(new Facility),
            'articles_per_month' => $this->monthlyCount(new Article),
            'stories_per_month' => $this->monthlyCount(new Story),
            'appointments_per_month' => $this->monthlyCount(new Appointment),
            'job_posts_per_month' => $this->monthlyCount(new JobPost),
            'donations_per_month' => $this->monthlyCount(new Donation),
            'top_categories' => $this->topCategories(),
            'top_departments' => $this->topDepartments(),
            'top_facilities' => $this->topFacilities(),
        ];
    }

    public function monthlyCount($model): Collection
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

        $raw = $model
            ->where('created_at', '>=', $now->copy()->subMonths(12)->startOfMonth())
            ->selectRaw("{$dateFormat} as month, COUNT(*) as total")
            ->groupBy(DB::raw($dateFormat))
            ->orderBy('month')
            ->pluck('total', 'month');

        return $months->map(fn (string $m) => [
            'label' => Carbon::createFromFormat('Y-m', $m)->format('M'),
            'value' => (int) ($raw[$m] ?? 0),
        ]);
    }

    public function topCategories(int $limit = 10): Collection
    {
        return Category::withCount('articles')
            ->orderByDesc('articles_count')
            ->limit($limit)
            ->get()
            ->map(fn ($c) => [
                'uuid' => $c->uuid,
                'name' => $c->getTranslations('name'),
                'count' => $c->articles_count,
            ]);
    }

    public function topDepartments(int $limit = 10): Collection
    {
        return Department::withCount('facilityStaff')
            ->orderByDesc('facility_staff_count')
            ->limit($limit)
            ->get()
            ->map(fn ($d) => [
                'uuid' => $d->uuid,
                'name' => $d->getTranslations('name'),
                'facility_name' => $d->facility?->getTranslations('name'),
                'staff_count' => $d->facility_staff_count,
            ]);
    }

    public function topFacilities(int $limit = 10): Collection
    {
        return Facility::withCount('facilityStaff')
            ->orderByDesc('facility_staff_count')
            ->limit($limit)
            ->get()
            ->map(fn ($f) => [
                'uuid' => $f->uuid,
                'name' => $f->getTranslations('name'),
                'type' => $f->facility_type,
                'staff_count' => $f->facility_staff_count,
            ]);
    }
}
