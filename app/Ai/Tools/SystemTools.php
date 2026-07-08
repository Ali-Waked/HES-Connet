<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Facility;
use Illuminate\Support\Facades\DB;

class GetUsersTool extends BaseTool
{
    public function name(): string
    {
        return 'get_users';
    }

    public function description(): string
    {
        return 'Get users from the system. Can filter by role, status, or search term.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'role' => [
                    'type' => 'string',
                    'description' => 'Filter by role slug (super_admin, facility_admin, etc.)',
                    'enum' => ['super_admin', 'facility_admin', 'doctor', 'patient'],
                ],
                'search' => [
                    'type' => 'string',
                    'description' => 'Search by name or email',
                ],
                'limit' => [
                    'type' => ['integer', 'string'],
                    'description' => 'Maximum number of users to return',
                ],
            ],
        ];
    }

    public function handle(array $arguments): mixed
    {
        $query = DB::table('users')
            ->select('id', 'name', 'email', 'created_at')
            ->limit(min($arguments['limit'] ?? 10, 50));

        if (! empty($arguments['search'])) {
            $term = $arguments['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        return $query->get()->toArray();
    }
}

class GetFacilitiesTool extends BaseTool
{
    public function name(): string
    {
        return 'get_facilities';
    }

    public function description(): string
    {
        return 'Get facilities/clinics from the system. Can filter by type, status, city, or search by name.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'facility_type' => [
                    'type' => 'string',
                    'description' => 'Filter by facility type',
                    'enum' => ['hospital', 'clinic', 'pharmacy', 'laboratory'],
                ],
                'city_id' => [
                    'type' => ['integer', 'string'],
                    'description' => 'Filter by city ID',
                ],
                'search' => [
                    'type' => 'string',
                    'description' => 'Search by name',
                ],
                'limit' => [
                    'type' => ['integer', 'string'],
                    'description' => 'Maximum number of facilities to return',
                ],
            ],
        ];
    }

    public function handle(array $arguments): mixed
    {
        $query = Facility::query()->with('city')
            ->select('id', 'uuid', 'name', 'facility_type', 'city_id', 'latitude', 'longitude', 'approval_status');

        if (! empty($arguments['facility_type'])) {
            $query->where('facility_type', $arguments['facility_type']);
        }

        if (! empty($arguments['city_id'])) {
            $query->where('city_id', $arguments['city_id']);
        }

        if (! empty($arguments['search'])) {
            $query->where('name', 'like', "%{$arguments['search']}%");
        }

        return $query->limit(min($arguments['limit'] ?? 10, 50))
            ->get()
            ->toArray();
    }
}

class GetDonationsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_donations';
    }

    public function description(): string
    {
        return 'Get donation records. Can filter by story, donor, or date range.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'story_id' => [
                    'type' => ['integer', 'string'],
                    'description' => 'Filter by story ID',
                ],
                'donor_id' => [
                    'type' => ['integer', 'string'],
                    'description' => 'Filter by donor user ID',
                ],
                'limit' => [
                    'type' => ['integer', 'string'],
                    'description' => 'Maximum number of records',
                ],
            ],
        ];
    }

    public function handle(array $arguments): mixed
    {
        $query = DB::table('donations')
            ->select('id', 'story_id', 'donor_id', 'amount', 'currency', 'paid_at', 'created_at')
            ->limit(min($arguments['limit'] ?? 10, 50));

        if (! empty($arguments['story_id'])) {
            $query->where('story_id', $arguments['story_id']);
        }

        if (! empty($arguments['donor_id'])) {
            $query->where('donor_id', $arguments['donor_id']);
        }

        return $query->orderBy('created_at', 'desc')->get()->toArray();
    }
}

class GetReportsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_reports';
    }

    public function description(): string
    {
        return 'Get system reports and analytics data. Returns platform stats including user counts, facility counts, appointment counts, and revenue data.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'report_type' => [
                    'type' => 'string',
                    'description' => 'Type of report to generate',
                    'enum' => ['summary', 'users', 'facilities', 'appointments', 'revenue'],
                ],
                'period' => [
                    'type' => 'string',
                    'description' => 'Time period for the report',
                    'enum' => ['all', 'today', 'this_week', 'this_month', 'this_year'],
                ],
            ],
        ];
    }

    public function handle(array $arguments): mixed
    {
        $type = $arguments['report_type'] ?? 'summary';
        $period = $arguments['period'] ?? 'all';

        $dateCondition = match ($period) {
            'today' => now()->startOfDay(),
            'this_week' => now()->startOfWeek(),
            'this_month' => now()->startOfMonth(),
            'this_year' => now()->startOfYear(),
            default => null,
        };

        return match ($type) {
            'users' => [
                'total' => DB::table('users')->when($dateCondition, fn ($q) => $q->where('created_at', '>=', $dateCondition))->count(),
                'patients' => DB::table('patients')->when($dateCondition, fn ($q) => $q->where('created_at', '>=', $dateCondition))->count(),
                'staff' => DB::table('staff')->when($dateCondition, fn ($q) => $q->where('created_at', '>=', $dateCondition))->count(),
            ],
            'facilities' => [
                'total' => DB::table('facilities')->when($dateCondition, fn ($q) => $q->where('created_at', '>=', $dateCondition))->count(),
                'hospitals' => DB::table('facilities')->where('facility_type', 'hospital')->when($dateCondition, fn ($q) => $q->where('created_at', '>=', $dateCondition))->count(),
                'clinics' => DB::table('facilities')->where('facility_type', 'clinic')->when($dateCondition, fn ($q) => $q->where('created_at', '>=', $dateCondition))->count(),
            ],
            'appointments' => [
                'total' => DB::table('appointments')->when($dateCondition, fn ($q) => $q->where('created_at', '>=', $dateCondition))->count(),
                'completed' => DB::table('appointments')->where('status', 'completed')->when($dateCondition, fn ($q) => $q->where('created_at', '>=', $dateCondition))->count(),
                'cancelled' => DB::table('appointments')->where('status', 'cancelled')->when($dateCondition, fn ($q) => $q->where('created_at', '>=', $dateCondition))->count(),
            ],
            'revenue' => [
                'total_donations' => DB::table('donations')->when($dateCondition, fn ($q) => $q->where('created_at', '>=', $dateCondition))->sum('amount'),
                'total_payments' => DB::table('payments')->where('status', 'completed')->when($dateCondition, fn ($q) => $q->where('created_at', '>=', $dateCondition))->sum('amount'),
            ],
            default => [
                'total_users' => DB::table('users')->count(),
                'total_patients' => DB::table('patients')->count(),
                'total_staff' => DB::table('staff')->count(),
                'total_facilities' => DB::table('facilities')->count(),
                'total_appointments' => DB::table('appointments')->count(),
                'total_donations' => DB::table('donations')->count(),
            ],
        };
    }
}
