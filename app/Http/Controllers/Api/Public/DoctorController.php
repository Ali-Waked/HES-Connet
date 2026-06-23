<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\Doctor\DoctorRequest;
use App\Http\Resources\Public\DoctorCollection;
use App\Http\Resources\Public\ShowDoctorResource;
use App\Models\Appointment;
use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\Staff;
use App\Models\StaffUnavailability;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index(DoctorRequest $request): DoctorCollection
    {
        info('hi');
        $doctors = Staff::query()
            ->with('facilities')
            ->withCount('facilities as facilities_count')
            ->doctors()
            ->when(
                $request->search,
                fn (Builder $query, string $search) => $query->where(function (Builder $q) use ($search) {
                    $q->whereHas('user', fn (Builder $uq) => $uq
                        ->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%")
                    )
                        ->orWhere('specialization->en', 'like', "%{$search}%")
                        ->orWhere('specialization->ar', 'like', "%{$search}%")
                        ->orWhere('bio->en', 'like', "%{$search}%")
                        ->orWhere('bio->ar', 'like', "%{$search}%");
                })
            )
            ->when(
                $request->specialization,
                fn (Builder $query, string $spec) => $query->where(function (Builder $q) use ($spec) {
                    $q->where('specialization->en', 'like', "%{$spec}%")
                        ->orWhere('specialization->ar', 'like', "%{$spec}%");
                })
            )
            ->when(
                $request->facility_id,
                fn (Builder $query, string $uuid) => $query->whereHas('facilities', fn (Builder $q) => $q
                    ->where('uuid', $uuid)
                )
            )
            ->latest('id')
            ->paginate($request->per_page ?? 15);
        info($doctors);

        return new DoctorCollection($doctors);
    }

    public function show(Staff $staff): ShowDoctorResource
    {
        info('show');
        abort_unless(
            $staff->user->hasSystemRole('super_admin')
            || $staff->facilityStaff()->whereNull('ended_at')->whereHas('role', fn ($q) => $q->where('slug', 'doctor'))->exists(),
            404
        );

        $staff->load([
            'facilities',
            'departments',
            'headFacilities',
        ]);

        return new ShowDoctorResource($staff);
    }

    // public function schedule(Staff $staff)
    // {
    //     return $staff->schedules;
    // }

    public function availableDays(Facility $facility, Staff $staff)
    {
        $from = Carbon::today();
        $to = Carbon::today()->addDays(30);

        $facilityStaff = FacilityStaff::where('facility_id', $facility->id)
            ->where('staff_id', $staff->id)
            ->firstOrFail();

        $schedules = $facilityStaff->schedules->keyBy('day_of_week');

        $appointments = Appointment::where('facility_staff_id', $facilityStaff->id)
            ->whereBetween('start_at', [$from, $to->copy()->endOfDay()])
            ->get();

        $unavailabilities = StaffUnavailability::where('staff_id', $staff->id)
            ->whereBetween('start_at', [$from, $to->copy()->endOfDay()])
            ->get();

        $days = [];

        foreach (CarbonPeriod::create($from, $to) as $date) {

            $schedule = $schedules[$date->dayOfWeek] ?? null;

            if (! $schedule) {
                continue;
            }

            if ($this->isDayBlocked($date, $unavailabilities)) {
                continue;
            }

            if ($this->hasAvailableSlot(
                $date,
                $schedule,
                $appointments,
                $unavailabilities
            )) {
                $days[] = $date->toDateString();
            }
        }

        return response()->json($days);
    }

    public function availableSlots(
        Request $request,
        Facility $facility,
        Staff $staff
    ): array {
        $date = Carbon::parse($request->query('date'));

        $schedule = $staff->schedules()
            ->where('day_of_week', $date->dayOfWeek)
            ->first();

        if (! $schedule) {
            return [];
        }

        $facilityStaff = FacilityStaff::query()
            ->where('facility_id', $facility->id)
            ->where('staff_id', $staff->id)
            ->firstOrFail();

        $slotDuration = $schedule->slot_duration;

        $slotStart = Carbon::parse(
            $date->toDateString().' '.$schedule->start_time
        );

        $scheduleEnd = Carbon::parse(
            $date->toDateString().' '.$schedule->end_time
        );

        $appointments = Appointment::query()
            ->where('facility_staff_id', $facilityStaff->id)
            ->whereDate('start_at', $date)
            ->get();

        $unavailabilities = StaffUnavailability::query()
            ->where('staff_id', $staff->id)
            ->where(function ($query) use ($date) {
                $query
                    ->whereDate('start_at', $date)
                    ->orWhereDate('end_at', $date);
            })
            ->get();

        $slots = [];

        while ($slotStart->copy()->addMinutes($slotDuration)->lte($scheduleEnd)) {

            $slotEnd = $slotStart->copy()->addMinutes($slotDuration);

            $isBooked = $appointments->contains(
                fn (Appointment $appointment) => $slotStart < Carbon::parse($appointment->end_at)
                    && $slotEnd > Carbon::parse($appointment->start_at)
            );

            $isUnavailable = $unavailabilities->contains(
                fn (StaffUnavailability $unavailability) => $slotStart < Carbon::parse($unavailability->end_at)
                    && $slotEnd > Carbon::parse($unavailability->start_at)
            );

            if (! $isBooked && ! $isUnavailable) {
                $slots[] = [
                    'start_at' => $slotStart->toDateTimeString(),
                    'end_at' => $slotEnd->toDateTimeString(),
                ];
            }

            $slotStart->addMinutes($slotDuration);
        }
        info($slots);

        return $slots;
    }

    public function facilities(Staff $staff)
    {
        info('facilities');

        return $staff->facilities;
    }

    private function isDayBlocked(Carbon $date, $unavailabilities): bool
    {
        return $unavailabilities->contains(fn ($u) => $date->between(
            Carbon::parse($u->start_at)->startOfDay(),
            Carbon::parse($u->end_at)->endOfDay()
        )
        );
    }

    private function hasAvailableSlot($date, $schedule, $appointments, $unavailabilities): bool
    {
        $slotDuration = $schedule->slot_duration;

        $start = Carbon::parse($date->toDateString().' '.$schedule->start_time);
        $end = Carbon::parse($date->toDateString().' '.$schedule->end_time);

        while ($start->copy()->addMinutes($slotDuration)->lte($end)) {

            $slotEnd = $start->copy()->addMinutes($slotDuration);

            $conflict = $appointments->contains(fn ($a) => $start < $a->end_at && $slotEnd > $a->start_at
            );

            $blocked = $unavailabilities->contains(fn ($u) => $start < $u->end_at && $slotEnd > $u->start_at
            );

            if (! $conflict && ! $blocked) {
                return true;
            }

            $start->addMinutes($slotDuration);
        }

        return false;
    }

    // public function store(Request $request)
    // {
    //     $facilityStaff = FacilityStaff::findOrFail($request->facility_staff_id);

    //     return Appointment::create([
    //         'facility_staff_id' => $facilityStaff->id,
    //         'patient_id' => auth()->id(),
    //         'start_at' => $request->start_at,
    //         'end_at' => $request->end_at,
    //         'reason' => $request->reason,
    //         'status' => 'pending',
    //     ]);
    // }
}
