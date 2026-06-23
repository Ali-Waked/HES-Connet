<?php

namespace App\Http\Controllers\Api\Public;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PublicAppointmentResource;
use App\Models\Appointment;
use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\Staff;
use App\Models\StaffUnavailability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $patient = auth()->user()?->patient;

        if (! $patient) {
            return response()->json([]);
        }

        $query = $patient->appointments()
            ->with([
                'review',
                'facilityStaff.facility',
                'facilityStaff.staff',
            ]);

        // 🔥 STATUS FILTER
        if ($request->filled('status')) {
            $query->whereIn(
                'status',
                AppointmentStatus::fromFilter($request->status)
            );
        }

        return PublicAppointmentResource::collection(
            $query->latest('start_at')->paginate(20)
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_uuid' => ['required', 'exists:facilities,uuid'],
            'doctor_uuid' => ['required', 'exists:staff,uuid'],
            'start_at' => ['required', 'date', 'after:now'],
            'reason' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated) {

            // 1. Resolve facility + doctor
            $facility = Facility::where('uuid', $validated['facility_uuid'])->firstOrFail();

            $doctor = Staff::where('uuid', $validated['doctor_uuid'])->firstOrFail();

            // 2. FacilityStaff context
            $facilityStaff = FacilityStaff::where('facility_id', $facility->id)
                ->where('staff_id', $doctor->id)
                ->first();

            if (! $facilityStaff) {
                throw ValidationException::withMessages([
                    'facility_uuid' => __('Doctor is not assigned to this facility.'),
                ]);
            }

            // 3. Time setup
            $startAt = Carbon::parse($validated['start_at']);
            $dayOfWeek = $startAt->dayOfWeek;

            // 4. Schedule
            $schedule = $facilityStaff->schedules()
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->first();

            if (! $schedule) {
                throw ValidationException::withMessages([
                    'start_at' => __('Doctor is not available on this day.'),
                ]);
            }

            // 5. Working hours validation
            $scheduleStart = Carbon::parse($startAt->toDateString().' '.$schedule->start_time);
            $scheduleEnd = Carbon::parse($startAt->toDateString().' '.$schedule->end_time);

            if ($startAt->lt($scheduleStart) || $startAt->gte($scheduleEnd)) {
                throw ValidationException::withMessages([
                    'start_at' => __('Selected time is outside working hours.'),
                ]);
            }

            // 6. Slot alignment
            $diffMinutes = $scheduleStart->diffInMinutes($startAt);

            if ($diffMinutes % $schedule->slot_duration !== 0) {
                throw ValidationException::withMessages([
                    'start_at' => __('Invalid slot selected.'),
                ]);
            }

            // 7. End time
            $endAt = $startAt->copy()->addMinutes($schedule->slot_duration);

            // 8. Staff global unavailability
            $isUnavailable = StaffUnavailability::where('staff_id', $doctor->id)
                ->where('start_at', '<', $endAt)
                ->where('end_at', '>', $startAt)
                ->exists();

            if ($isUnavailable) {
                throw ValidationException::withMessages([
                    'start_at' => __('Doctor is unavailable.'),
                ]);
            }

            // 9. Appointment overlap check
            $alreadyBooked = Appointment::where('facility_staff_id', $facilityStaff->id)
                ->where('status', '!=', AppointmentStatus::CANCELLED)
                ->where('start_at', '<', $endAt)
                ->where('end_at', '>', $startAt)
                ->exists();

            if ($alreadyBooked) {
                throw ValidationException::withMessages([
                    'start_at' => __('This slot has already been booked.'),
                ]);
            }

            // 10. Prevent multiple active appointments for same patient
            $patient = auth()->user()->patient()->firstOrCreate();

            $hasActiveAppointment = Appointment::where('patient_id', $patient->id)
                ->where('facility_staff_id', $facilityStaff->id)
                ->whereIn('status', AppointmentStatus::activeStatuses())
                ->exists();

            if ($hasActiveAppointment) {
                throw ValidationException::withMessages([
                    'facility_uuid' => __('You already have an active appointment with this doctor.'),
                ]);
            }

            // 11. Create appointment
            $appointment = Appointment::create([
                'uuid' => Str::uuid(),
                'facility_id' => $facility->id,
                'facility_staff_id' => $facilityStaff->id,
                'patient_id' => $patient->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'status' => AppointmentStatus::SCHEDULED,
                'reason' => $validated['reason'] ?? null,
            ]);

            return response()->json([
                'message' => __('Appointment booked successfully.'),
                'data' => $appointment,
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
