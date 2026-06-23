<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\StaffUnavailability;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FacilityDashboardService
{
    public function getDashboard(int $facilityId): array
    {
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();
        $facilityId = (int) $facilityId;

        $staffIds = FacilityStaff::where('facility_id', $facilityId)->pluck('staff_id');

        $appointmentsToday = Cache::remember("dashboard.{$facilityId}.appointments_today", 120, function () use ($facilityId, $today) {
            return Appointment::where('facility_id', $facilityId)
                ->where('start_at', '>=', $today)
                ->where('start_at', '<', $today->copy()->addDay())
                ->count();
        });

        $appointmentsMonth = Cache::remember("dashboard.{$facilityId}.appointments_month", 300, function () use ($facilityId, $monthStart) {
            return Appointment::where('facility_id', $facilityId)
                ->where('start_at', '>=', $monthStart)
                ->count();
        });

        $completedAppointments = Cache::remember("dashboard.{$facilityId}.completed", 300, function () use ($facilityId) {
            return Appointment::where('facility_id', $facilityId)
                ->where('status', AppointmentStatus::COMPLETED)
                ->count();
        });

        $cancelledAppointments = Cache::remember("dashboard.{$facilityId}.cancelled", 300, function () use ($facilityId) {
            return Appointment::where('facility_id', $facilityId)
                ->where('status', AppointmentStatus::CANCELLED)
                ->count();
        });

        $totalAppointments = Cache::remember("dashboard.{$facilityId}.total_appts", 300, function () use ($facilityId) {
            return Appointment::where('facility_id', $facilityId)->count();
        });

        $noShowCount = Cache::remember("dashboard.{$facilityId}.no_show", 300, function () use ($facilityId) {
            return Appointment::where('facility_id', $facilityId)
                ->where('status', AppointmentStatus::NO_SHOW)
                ->count();
        });

        $noShowRate = $totalAppointments > 0
            ? round(($noShowCount / $totalAppointments) * 100, 1)
            : 0;

        $activeDoctorsCount = Cache::remember("dashboard.{$facilityId}.active_doctors", 600, function () use ($staffIds, $facilityId) {
            return Staff::whereIn('id', $staffIds)
                ->where('status', AccountStatus::ACTIVE)
                ->whereHas('facilityStaff', fn ($q) => $q
                    ->where('facility_id', $facilityId)
                    ->whereNull('ended_at')
                    ->whereHas('role', fn ($rq) => $rq->where('slug', 'doctor'))
                )
                ->count();
        });

        $totalPatientsCount = Cache::remember("dashboard.{$facilityId}.total_patients", 600, function () use ($facilityId) {
            return Appointment::where('facility_id', $facilityId)
                ->distinct('patient_id')
                ->count('patient_id');
        });

        return [
            'total_appointments_today' => $appointmentsToday,
            'total_appointments_month' => $appointmentsMonth,
            'completed_appointments' => $completedAppointments,
            'cancelled_appointments' => $cancelledAppointments,
            'no_show_rate' => $noShowRate,
            'active_doctors_count' => $activeDoctorsCount,
            'total_patients_count' => $totalPatientsCount,
            'revenue_total' => null,
        ];
    }

    public function getLiveAppointments(int $facilityId): Collection
    {
        $today = now()->startOfDay();

        return Appointment::with(['staff.user', 'patient.user', 'facility'])
            ->where('facility_id', $facilityId)
            ->where('start_at', '>=', $today)
            ->where('start_at', '<', $today->copy()->addDay())
            ->orderBy('start_at')
            ->get();
    }

    public function getDoctorPerformance(int $facilityId): Collection
    {
        $staffIds = FacilityStaff::where('facility_id', $facilityId)->pluck('staff_id');

        $staff = Staff::with('user')
            ->whereIn('id', $staffIds)
            ->whereHas('facilityStaff', fn ($q) => $q
                ->where('facility_id', $facilityId)
                ->whereNull('ended_at')
                ->whereHas('role', fn ($rq) => $rq->where('slug', 'doctor'))
            )
            ->get();

        $thirtyDaysAgo = now()->subDays(30);

        return $staff->map(function (Staff $doctor) use ($facilityId, $thirtyDaysAgo) {
            $appointments = Appointment::where('facility_id', $facilityId)
                ->where('staff_id', $doctor->id)
                ->get();

            $total = $appointments->count();
            $completed = $appointments->where('status->value', AppointmentStatus::COMPLETED->value)->count();
            $cancelled = $appointments->where('status->value', AppointmentStatus::CANCELLED->value)->count();
            $noShow = $appointments->where('status->value', AppointmentStatus::NO_SHOW->value)->count();

            $recentAppointments = Appointment::where('facility_id', $facilityId)
                ->where('staff_id', $doctor->id)
                ->where('start_at', '>=', $thirtyDaysAgo)
                ->count();

            $averagePerDay = $recentAppointments > 0
                ? round($recentAppointments / 30, 1)
                : 0;

            return [
                'doctor' => [
                    'uuid' => $doctor->uuid,
                    'name' => $doctor->user->getTranslations('name'),
                    'specialization' => $doctor->getTranslations('specialization'),
                ],
                'total_appointments' => $total,
                'completed' => $completed,
                'cancelled' => $cancelled,
                'no_show' => $noShow,
                'average_per_day' => $averagePerDay,
            ];
        })->sortByDesc('total_appointments')->values();
    }

    public function getPatientOverview(int $facilityId): array
    {
        $monthStart = now()->startOfMonth();

        $totalPatients = Appointment::where('facility_id', $facilityId)
            ->distinct('patient_id')
            ->count('patient_id');

        $newThisMonth = Appointment::where('facility_id', $facilityId)
            ->where('start_at', '>=', $monthStart)
            ->distinct('patient_id')
            ->count('patient_id');

        $returningPatients = Appointment::where('facility_id', $facilityId)
            ->select('patient_id', DB::raw('count(*) as visits'))
            ->groupBy('patient_id')
            ->having('visits', '>', 1)
            ->count();

        $topPatients = Appointment::with('patient.user')
            ->where('facility_id', $facilityId)
            ->select('patient_id', DB::raw('count(*) as total_visits'))
            ->groupBy('patient_id')
            ->orderByDesc('total_visits')
            ->take(10)
            ->get()
            ->map(fn ($row) => [
                'patient_uuid' => $row->patient->user->uuid,
                'patient_name' => $row->patient->user->getTranslations('name'),
                'total_visits' => (int) $row->total_visits,
            ]);

        return [
            'total_patients' => $totalPatients,
            'new_patients_this_month' => $newThisMonth,
            'returning_patients' => $returningPatients,
            'top_patients' => $topPatients,
        ];
    }

    public function getStaff(int $facilityId): Collection
    {
        $staffIds = FacilityStaff::where('facility_id', $facilityId)->pluck('staff_id');

        $staff = Staff::with(['user', 'position', 'schedules'])
            ->whereIn('id', $staffIds)
            ->get();

        $departments = FacilityStaff::where('facility_id', $facilityId)
            ->with('department')
            ->get()
            ->keyBy('staff_id');

        return $staff->map(function (Staff $member) use ($departments) {
            $department = $departments->get($member->id)?->department;

            return [
                'uuid' => $member->uuid,
                'name' => $member->user->getTranslations('name'),
                'email' => $member->user->email,
                'specialization' => $member->getTranslations('specialization'),
                'position' => $member->whenLoaded('position', fn () => [
                    'uuid' => $member->position->uuid,
                    'name' => $member->position->getTranslations('name'),
                ]),
                'department' => $department ? [
                    'uuid' => $department->uuid,
                    'name' => $department->getTranslations('name'),
                ] : null,
                'has_schedule' => $member->schedules->isNotEmpty(),
                'status' => $member->status?->value,
            ];
        });
    }

    public function getScheduleOverview(int $facilityId): array
    {
        $staffIds = FacilityStaff::where('facility_id', $facilityId)->pluck('staff_id');

        $schedules = StaffSchedule::with('staff.user')
            ->whereIn('staff_id', $staffIds)
            ->where('facility_id', $facilityId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('staff_id');

        $unavailabilities = StaffUnavailability::with('staff.user')
            ->whereIn('staff_id', $staffIds)
            ->where('end_at', '>=', now())
            ->orderBy('start_at')
            ->get();

        $weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return [
            'schedules' => $schedules->map(fn ($staffSchedules, $staffId) => [
                'staff' => [
                    'uuid' => $staffSchedules->first()->staff->uuid,
                    'name' => $staffSchedules->first()->staff->user->getTranslations('name'),
                ],
                'weekly_hours' => $staffSchedules->groupBy('day_of_week')->map(fn ($daySchedules, $day) => [
                    'day' => $weekDays[(int) $day] ?? $day,
                    'day_of_week' => (int) $day,
                    'slots' => $daySchedules->map(fn ($s) => [
                        'start_time' => $s->start_time,
                        'end_time' => $s->end_time,
                        'slot_duration' => $s->slot_duration,
                    ]),
                ])->values(),
            ])->values(),
            'upcoming_unavailabilities' => $unavailabilities->map(fn ($u) => [
                'staff' => [
                    'uuid' => $u->staff->uuid,
                    'name' => $u->staff->user->getTranslations('name'),
                ],
                'start_at' => $u->start_at,
                'end_at' => $u->end_at,
                'reason' => $u->reason,
            ]),
        ];
    }

    public function getAnalytics(int $facilityId): array
    {
        $days = (int) request('days', 30);

        return Cache::remember("dashboard.{$facilityId}.analytics.{$days}", 900, function () use ($facilityId, $days) {
            $since = now()->subDays($days)->startOfDay();

            $appointmentsPerDay = Appointment::where('facility_id', $facilityId)
                ->where('start_at', '>=', $since)
                ->select(DB::raw('DATE(start_at) as date'), DB::raw('count(*) as total'))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $dateRange = collect();
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $dateRange->push([
                    'date' => $date,
                    'total' => (int) ($appointmentsPerDay[$date]->total ?? 0),
                ]);
            }

            $peakHours = Appointment::where('facility_id', $facilityId)
                ->where('start_at', '>=', $since)
                ->select(DB::raw("DATE_FORMAT(start_at, '%H:00') as hour"), DB::raw('count(*) as total'))
                ->groupBy('hour')
                ->orderByDesc('total')
                ->take(5)
                ->get();

            $staffIds = FacilityStaff::where('facility_id', $facilityId)->pluck('staff_id');

            $mostBookedDoctors = Appointment::with('staff.user')
                ->where('facility_id', $facilityId)
                ->whereIn('staff_id', $staffIds)
                ->where('start_at', '>=', $since)
                ->select('staff_id', DB::raw('count(*) as total'))
                ->groupBy('staff_id')
                ->orderByDesc('total')
                ->take(5)
                ->get()
                ->map(fn ($row) => [
                    'doctor' => [
                        'uuid' => $row->staff->uuid,
                        'name' => $row->staff->user->getTranslations('name'),
                    ],
                    'total_appointments' => (int) $row->total,
                ]);

            $cancellationTrend = Appointment::where('facility_id', $facilityId)
                ->where('start_at', '>=', $since)
                ->select(
                    DB::raw('DATE(start_at) as date'),
                    DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled"),
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn ($row) => [
                    'date' => $row->date,
                    'cancellation_rate' => $row->total > 0
                        ? round(($row->cancelled / $row->total) * 100, 1)
                        : 0,
                ]);

            return [
                'appointments_per_day' => $dateRange,
                'peak_hours' => $peakHours,
                'most_booked_doctors' => $mostBookedDoctors,
                'cancellation_rate_trend' => $cancellationTrend,
            ];
        });
    }

    public function getAlerts(int $facilityId): array
    {
        $staffIds = FacilityStaff::where('facility_id', $facilityId)->pluck('staff_id');

        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $overbookedDoctors = Staff::with('user')
            ->whereIn('id', $staffIds)
            ->whereHas('appointmentsAsDoctor', function ($q) use ($facilityId, $todayStart, $todayEnd) {
                $q->where('facility_id', $facilityId)
                    ->where('start_at', '>=', $todayStart)
                    ->where('start_at', '<=', $todayEnd)
                    ->whereNotIn('status', [
                        AppointmentStatus::CANCELLED->value,
                        AppointmentStatus::NO_SHOW->value,
                    ]);
            })
            ->get()
            ->map(function (Staff $doctor) use ($facilityId, $todayStart, $todayEnd) {
                $count = Appointment::where('facility_id', $facilityId)
                    ->where('staff_id', $doctor->id)
                    ->where('start_at', '>=', $todayStart)
                    ->where('start_at', '<=', $todayEnd)
                    ->whereNotIn('status', [
                        AppointmentStatus::CANCELLED->value,
                        AppointmentStatus::NO_SHOW->value,
                    ])
                    ->count();

                return [
                    'type' => 'overbooked',
                    'severity' => $count >= 15 ? 'high' : ($count >= 10 ? 'medium' : 'low'),
                    'message' => __(':name has :count appointments today.', [
                        'name' => $doctor->user->name,
                        'count' => $count,
                    ]),
                    'doctor' => [
                        'uuid' => $doctor->uuid,
                        'name' => $doctor->user->getTranslations('name'),
                    ],
                    'appointments_count' => $count,
                ];
            })
            ->filter(fn ($alert) => $alert['appointments_count'] >= 8)
            ->values();

        $unavailableToday = StaffUnavailability::with('staff.user')
            ->whereIn('staff_id', $staffIds)
            ->where('start_at', '<', $todayEnd)
            ->where('end_at', '>', $todayStart)
            ->get()
            ->map(fn ($u) => [
                'type' => 'unavailable',
                'severity' => 'medium',
                'message' => __(':name is unavailable today.', [
                    'name' => $u->staff->user->name,
                ]),
                'doctor' => [
                    'uuid' => $u->staff->uuid,
                    'name' => $u->staff->user->getTranslations('name'),
                ],
                'period' => [
                    'start_at' => $u->start_at,
                    'end_at' => $u->end_at,
                ],
                'reason' => $u->reason,
            ]);

        $highCancellationDoctors = Staff::with('user')
            ->whereIn('id', $staffIds)
            ->get()
            ->filter(function (Staff $doctor) use ($facilityId) {
                $total = Appointment::where('facility_id', $facilityId)
                    ->where('staff_id', $doctor->id)
                    ->where('start_at', '>=', now()->subDays(30))
                    ->count();

                if ($total < 5) {
                    return false;
                }

                $cancelled = Appointment::where('facility_id', $facilityId)
                    ->where('staff_id', $doctor->id)
                    ->where('start_at', '>=', now()->subDays(30))
                    ->where('status', AppointmentStatus::CANCELLED)
                    ->count();

                $rate = ($cancelled / $total) * 100;

                return $rate > 20;
            })
            ->map(fn (Staff $doctor) => [
                'type' => 'high_cancellation_rate',
                'severity' => 'high',
                'message' => __(':name has a high cancellation rate.', [
                    'name' => $doctor->user->name,
                ]),
                'doctor' => [
                    'uuid' => $doctor->uuid,
                    'name' => $doctor->user->getTranslations('name'),
                ],
            ])
            ->values();

        return array_merge($overbookedDoctors->toArray(), $unavailableToday->toArray(), $highCancellationDoctors->toArray());
    }
}
