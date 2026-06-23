<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Appointment;
use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\StaffUnavailability;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    public function __construct(private readonly UuidResolver $uuid_resolver)
    {
    }

    public function getAvailableSlots(Staff $staff, string $facilityUuid, string $date): array
    {
        $facilityId = $this->uuid_resolver->resolve(Facility::class, $facilityUuid);

        $facilityStaff = FacilityStaff::query()
            ->where('staff_id', $staff->id)
            ->where('facility_id', $facilityId)
            ->active()
            ->firstOrFail();

        $carbonDate = Carbon::parse($date);
        $dayOfWeek = (int) $carbonDate->format('w');

        $schedules = $this->getActiveSchedules($facilityStaff->id, $dayOfWeek);
        if ($schedules->isEmpty()) {
            return [];
        }

        $unavailabilities = $this->getUnavailabilitiesForDate($facilityStaff->id, $carbonDate);
        $appointments = $this->getAppointmentsForDate($facilityStaff->id, $carbonDate);

        return $this->generateSlots($schedules, $unavailabilities, $appointments, $carbonDate);
    }

    private function getActiveSchedules(int $facilityStaffId, int $dayOfWeek): Collection
    {
        return StaffSchedule::query()
            ->where('facility_staff_id', $facilityStaffId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();
    }

    private function getUnavailabilitiesForDate(int $facilityStaffId, Carbon $date): Collection
    {
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();

        return StaffUnavailability::query()
            ->where('facility_staff_id', $facilityStaffId)
            ->where('start_at', '<', $dayEnd)
            ->where('end_at', '>', $dayStart)
            ->get();
    }

    private function getAppointmentsForDate(int $facilityStaffId, Carbon $date): Collection
    {
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();

        return Appointment::query()
            ->where('facility_staff_id', $facilityStaffId)
            ->where('start_at', '>=', $dayStart)
            ->where('end_at', '<=', $dayEnd)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('start_at')
            ->get();
    }

    private function generateSlots(
        Collection $schedules,
        Collection $unavailabilities,
        Collection $appointments,
        Carbon $date,
    ): array {
        $slots = [];

        foreach ($schedules as $schedule) {
            $scheduleStart = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->start_time);
            $scheduleEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->end_time);
            $duration = (int) $schedule->slot_duration;

            $current = $scheduleStart->copy();

            while ($current->copy()->addMinutes($duration)->lte($scheduleEnd)) {
                $slotStart = $current->copy();
                $slotEnd = $current->copy()->addMinutes($duration);

                $slot = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'start_at' => $slotStart->toDateTimeString(),
                    'end_at' => $slotEnd->toDateTimeString(),
                ];

                if ($this->isSlotAvailable($slotStart, $slotEnd, $unavailabilities, $appointments)) {
                    $slots[] = $slot;
                }

                $current->addMinutes($duration);
            }
        }

        return $slots;
    }

    private function isSlotAvailable(
        Carbon $slotStart,
        Carbon $slotEnd,
        Collection $unavailabilities,
        Collection $appointments,
    ): bool {
        foreach ($unavailabilities as $unavailability) {
            $uaStart = Carbon::parse($unavailability->start_at);
            $uaEnd = Carbon::parse($unavailability->end_at);

            if ($slotStart->lt($uaEnd) && $slotEnd->gt($uaStart)) {
                return false;
            }
        }

        foreach ($appointments as $appointment) {
            $apptStart = Carbon::parse($appointment->start_at);
            $apptEnd = Carbon::parse($appointment->end_at);

            if ($slotStart->lt($apptEnd) && $slotEnd->gt($apptStart)) {
                return false;
            }
        }

        return true;
    }
}
