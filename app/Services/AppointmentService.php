<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Appointment;
use App\Models\Facility;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use App\Enums\AppointmentStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    public function __construct(private readonly UuidResolver $uuid_resolver)
    {
    }

    public function paginate(
        int $perPage = 15,
        ?string $status = null,
        ?int $staffId = null,
        ?int $patientId = null,
    ): LengthAwarePaginator {
        return Appointment::query()
            ->with(['staff.user', 'patient.user', 'facility'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($staffId, fn ($query) => $query->where('staff_id', $staffId))
            ->when($patientId, fn ($query) => $query->where('patient_id', $patientId))
            ->latest('start_at')
            ->paginate($perPage);
    }

    public function create(array $data): Appointment
    {
        $data['staff_id'] = $this->uuid_resolver->resolve(Staff::class, $data['staff_uuid']);
        $data['patient_id'] = Patient::whereHas('user', fn ($q) => $q->where('uuid', $data['patient_uuid']))->firstOrFail()->id;
        $data['facility_id'] = $this->uuid_resolver->resolve(Facility::class, $data['facility_uuid']);
        $data['status'] = AppointmentStatus::SCHEDULED;

        $this->validateBooking(
            staffId: $data['staff_id'],
            facilityId: $data['facility_id'],
            startAt: $data['start_at'],
            endAt: $data['end_at'],
        );

        return Appointment::create($data);
    }

    public function show(Appointment $appointment): Appointment
    {
        return $appointment->load([
            'staff.user',
            'patient.user',
            'facility',
            'reschedules',
            'prescriptions.items.medicine',
        ]);
    }

    public function update(Appointment $appointment, array $data): Appointment
    {
        if (isset($data['start_at']) || isset($data['end_at'])) {
            $startAt = $data['start_at'] ?? $appointment->start_at->toDateTimeString();
            $endAt = $data['end_at'] ?? $appointment->end_at->toDateTimeString();

            $this->validateBooking(
                staffId: $appointment->staff_id,
                facilityId: $appointment->facility_id,
                startAt: $startAt,
                endAt: $endAt,
                excludeAppointmentId: $appointment->id,
            );
        }

        $appointment->update($data);

        return $appointment->refresh();
    }

    public function cancel(Appointment $appointment, ?string $reason = null): Appointment
    {
        $appointment->update([
            'status' => AppointmentStatus::CANCELLED,
            'cancellation_reason' => $reason,
        ]);

        return $appointment->refresh();
    }

    public function reschedule(
        Appointment $appointment,
        string $newStartAt,
        string $newEndAt,
        ?string $reason = null,
    ): Appointment {
        $this->validateBooking(
            staffId: $appointment->staff_id,
            facilityId: $appointment->facility_id,
            startAt: $newStartAt,
            endAt: $newEndAt,
            excludeAppointmentId: $appointment->id,
        );

        $appointment->reschedules()->create([
            'old_start_at' => $appointment->start_at,
            'old_end_at' => $appointment->end_at,
            'new_start_at' => $newStartAt,
            'new_end_at' => $newEndAt,
            'reason' => $reason,
        ]);

        $appointment->update([
            'start_at' => $newStartAt,
            'end_at' => $newEndAt,
            'status' => AppointmentStatus::RESCHEDULED,
        ]);

        return $appointment->refresh();
    }

    public function validateBooking(
        int $staffId,
        int $facilityId,
        string $startAt,
        string $endAt,
        ?int $excludeAppointmentId = null,
    ): void {
        $start = Carbon::parse($startAt);
        $end = Carbon::parse($endAt);

        if ($start->greaterThanOrEqualTo($end)) {
            throw ValidationException::withMessages([
                'end_at' => __('End time must be after start time.'),
            ]);
        }

        $dayOfWeek = (int) $start->format('w');

        $schedule = \App\Models\StaffSchedule::query()
            ->where('staff_id', $staffId)
            ->where('facility_id', $facilityId)
            ->where('day_of_week', $dayOfWeek)
            ->whereTime('start_time', '<=', $start->format('H:i'))
            ->whereTime('end_time', '>=', $end->format('H:i'))
            ->first();

        if (!$schedule) {
            throw ValidationException::withMessages([
                'start_at' => __('Appointment time is outside staff schedule hours.'),
            ]);
        }

        $appointmentDuration = $start->diffInMinutes($end);
        if ($appointmentDuration > $schedule->slot_duration) {
            throw ValidationException::withMessages([
                'end_at' => __('Appointment duration exceeds the allowed slot duration of :minutes minutes.', [
                    'minutes' => $schedule->slot_duration,
                ]),
            ]);
        }

        $overlap = Appointment::query()
            ->where('staff_id', $staffId)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_at', [$start, $end->copy()->subSecond()])
                    ->orWhereBetween('end_at', [$start->copy()->addSecond(), $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_at', '<=', $start)
                            ->where('end_at', '>=', $end);
                    });
            })
            ->whereNotIn('status', [
                AppointmentStatus::CANCELLED->value,
                AppointmentStatus::NO_SHOW->value,
            ])
            ->when($excludeAppointmentId, fn ($q) => $q->where('id', '!=', $excludeAppointmentId))
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_at' => __('This time slot overlaps with an existing appointment.'),
            ]);
        }

        $unavailable = \App\Models\StaffUnavailability::query()
            ->where('staff_id', $staffId)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_at', [$start, $end])
                    ->orWhereBetween('end_at', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_at', '<=', $start)
                            ->where('end_at', '>=', $end);
                    });
            })
            ->exists();

        if ($unavailable) {
            throw ValidationException::withMessages([
                'start_at' => __('Appointment falls within staff unavailable period.'),
            ]);
        }
    }
}
