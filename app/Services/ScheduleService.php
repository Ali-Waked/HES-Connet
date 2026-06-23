<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\StaffSchedule;
use App\Models\StaffUnavailability;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ScheduleService
{
    public function __construct(private readonly UuidResolver $uuid_resolver)
    {
    }

    public function paginate(int $perPage = 15, ?string $facilityUuid = null): LengthAwarePaginator
    {
        $query = StaffSchedule::query()
            ->with('facilityStaff.staff.user', 'facilityStaff.facility');

        if ($facilityUuid) {
            $facilityId = $this->uuid_resolver->resolve(Facility::class, $facilityUuid);
            $query->whereHas('facilityStaff', fn ($q) => $q->where('facility_id', $facilityId));
        }

        return $query->latest()->paginate($perPage);
    }
 public function create(array $data): void
{
    $facilityStaff = $this->uuid_resolver->model(
        FacilityStaff::class,
        $data['facility_staff_uuid']
    );

    $rows = [];

    foreach ($data['days_of_week'] as $day) {

        $this->preventOverlap(
            staffId: $facilityStaff->staff_id,
            dayOfWeek: $day,
            startTime: $data['start_time'],
            endTime: $data['end_time'],
        );

        $rows[] = [
            'facility_staff_id' => $facilityStaff->id,
            'day_of_week' => $day,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'slot_duration' => $data['slot_duration'],
            'is_active' => $data['is_active'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    StaffSchedule::insert($rows);
    }

     public function show(StaffSchedule $schedule): StaffSchedule
    {
        return $schedule->load('facilityStaff.staff.user', 'facilityStaff.facility');
    }

    public function update(StaffSchedule $schedule, array $data): StaffSchedule
    {
        if (isset($data['facility_staff_uuid'])) {
            $facilityStaff = $this->uuid_resolver->model(FacilityStaff::class, $data['facility_staff_uuid']);
            $data['facility_staff_id'] = $facilityStaff->id;
        }

        $facilityStaffId = $data['facility_staff_id'] ?? $schedule->facility_staff_id;
        $dayOfWeek = $data['day_of_week'] ?? $schedule->day_of_week;
        $startTime = $data['start_time'] ?? $schedule->start_time;
        $endTime = $data['end_time'] ?? $schedule->end_time;

        if (isset($data['day_of_week']) || isset($data['start_time']) || isset($data['end_time']) || isset($data['facility_staff_uuid'])) {
            $this->preventOverlap(
                facilityStaffId: $facilityStaffId,
                dayOfWeek: $dayOfWeek,
                startTime: $startTime,
                endTime: $endTime,
                excludeScheduleId: $schedule->id,
            );
        }

        $schedule->update($data);

        return $schedule->refresh()->load('facilityStaff.staff.user', 'facilityStaff.facility');
    }

    public function destroy(StaffSchedule $schedule): void
    {
        $schedule->delete();
    }

    public function calendar(string $facilityUuid, int $month, int $year): array
    {
        $facilityId = $this->uuid_resolver->resolve(Facility::class, $facilityUuid);
        $facility = Facility::findOrFail($facilityId);

        $facilityStaffIds = FacilityStaff::where('facility_id', $facilityId)->pluck('id');

        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $schedules = StaffSchedule::whereIn('facility_staff_id', $facilityStaffIds)
            ->where('is_active', true)
            ->get()
            ->groupBy('day_of_week');

        $unavailabilities = StaffUnavailability::whereIn('facility_staff_id', $facilityStaffIds)
            ->where('start_at', '<=', $endOfMonth)
            ->where('end_at', '>=', $startOfMonth)
            ->get();

        $days = [];

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dayOfWeek = (int) $date->format('w');
            $dateStr = $date->format('Y-m-d');

            $daySchedules = $schedules->get($dayOfWeek, collect());

            $isUnavailable = $unavailabilities->contains(function ($u) use ($date) {
                return $date->between($u->start_at, $u->end_at);
            });

            $days[] = [
                'date' => $dateStr,
                'day_of_week' => $dayOfWeek,
                'is_available' => $daySchedules->isNotEmpty() && !$isUnavailable,
                'is_today' => $date->isToday(),
                'is_past' => $date->isPast(),
                'slots' => $daySchedules->map(fn ($s) => [
                    'start_time' => $s->start_time,
                    'end_time' => $s->end_time,
                    'slot_duration' => $s->slot_duration,
                ])->values(),
            ];
        }

        return [
            'facility' => [
                'uuid' => $facility->uuid,
                'name' => $facility->getTranslations('name'),
            ],
            'month' => $month,
            'year' => $year,
            'days' => $days,
        ];
    }

    private function preventOverlap(
        int $staffId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $excludeScheduleId = null,
    ): void {
       $overlap = StaffSchedule::query()
    ->whereHas('facilityStaff', function ($q) use ($staffId) {
        $q->where('staff_id', $staffId);
    })
    ->where('day_of_week', $dayOfWeek)
    ->where('start_time', '<', $endTime)
    ->where('end_time', '>', $startTime)
    ->when(
        $excludeScheduleId,
        fn ($q) => $q->where('id', '!=', $excludeScheduleId)
    )
    ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_time' => __('This schedule overlaps with an existing schedule for this time period.'),
            ]);
        }
    }
}
