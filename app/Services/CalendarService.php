<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\StaffUnavailability;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class CalendarService
{
    /**
     * @return SupportCollection<int, array>
     */
    public function index(
        Staff $staff,
        ?Facility $facility,
        Carbon $weekStart,
        Carbon $weekEnd,
    ): SupportCollection {
        $facilityStaffQuery = $staff->facilityStaff()
            ->with('facility');

        if ($facility !== null) {
            $facilityStaffQuery->where('facility_id', $facility->id);
        }

        $facilityStaff = $facilityStaffQuery->get();

        if ($facilityStaff->isEmpty()) {
            return collect();
        }

        $facilityStaffIds = $facilityStaff->pluck('id');

        $facilityMap = $facilityStaff->keyBy('id')
            ->map(fn (FacilityStaff $fs) => [
                'uuid' => $fs->facility->uuid,
                'name' => $fs->facility->name,
            ]);

        $schedules = $this->loadSchedules($facilityStaffIds);
        $unavailabilities = $this->loadUnavailabilities($facilityStaffIds, $weekStart, $weekEnd);

        $events = collect();

        foreach ($schedules as $schedule) {
            $dates = $this->getDatesForDayOfWeek($schedule->day_of_week, $weekStart, $weekEnd);

            foreach ($dates as $date) {
                $start = $date->copy()->setTimeFromTimeString($schedule->start_time);
                $end = $date->copy()->setTimeFromTimeString($schedule->end_time);

                $facility = $facilityMap->get($schedule->facility_staff_id);

                $events->push([
                    'id' => $schedule->id,
                    'type' => 'schedule',
                    'facility_uuid' => $facility['uuid'] ?? null,
                    'facility_name' => $facility['name'] ?? null,
                    'title' => __('Working Hours'),
                    'start' => $start->toIso8601String(),
                    'end' => $end->toIso8601String(),
                    'day_of_week' => $schedule->day_of_week,
                    'color' => '#2563eb',
                ]);
            }
        }

        foreach ($unavailabilities as $unavailability) {
            $facility = $facilityMap->get($unavailability->facility_staff_id);

            $events->push([
                'id' => $unavailability->id,
                'type' => 'unavailability',
                'facility_uuid' => $facility['uuid'] ?? null,
                'facility_name' => $facility['name'] ?? null,
                'title' => $unavailability->reason,
                'start' => $unavailability->start_at->toIso8601String(),
                'end' => $unavailability->end_at->toIso8601String(),
                'reason' => $unavailability->reason,
                'status' => $unavailability->status->value,
                'color' => '#ef4444',
            ]);
        }

        return $events->sortBy('start')->values();
    }

    /**
     * @return Collection<int, StaffSchedule>
     */
    private function loadSchedules(SupportCollection $facilityStaffIds): Collection
    {
        return StaffSchedule::query()
            ->whereIn('facility_staff_id', $facilityStaffIds)
            ->where('is_active', true)
            ->get();
    }

    /**
     * @return Collection<int, StaffUnavailability>
     */
    private function loadUnavailabilities(
        SupportCollection $facilityStaffIds,
        Carbon $weekStart,
        Carbon $weekEnd,
    ): Collection {
        return StaffUnavailability::query()
            ->whereIn('facility_staff_id', $facilityStaffIds)
            ->where('start_at', '<', $weekEnd)
            ->where('end_at', '>', $weekStart)
            ->get();
    }

    /**
     * @return Carbon[]
     */
    private function getDatesForDayOfWeek(int $dayOfWeek, Carbon $weekStart, Carbon $weekEnd): array
    {
        $dates = [];
        $current = $weekStart->copy();

        while ($current <= $weekEnd) {
            if ((int) $current->format('w') === $dayOfWeek) {
                $dates[] = $current->copy();
            }
            $current->addDay();
        }

        return $dates;
    }
}
