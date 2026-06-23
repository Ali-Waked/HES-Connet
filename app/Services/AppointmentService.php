<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\StaffUnavailability;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    public function paginate(User $user, array $filters): LengthAwarePaginator
    {
        $query = Appointment::query()
            ->with([
                'facilityStaff.staff.user',
                'facilityStaff.facility',
                'patient.user',
                'review',
            ]);

        $query = $this->applyRoleScope($query, $user);
        $query = $this->applyFilters($query, $filters);

        return $query->latest('start_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function show(User $user, Appointment $appointment): Appointment
    {
        $this->authorizeAccess($user, $appointment);

        return $appointment->load([
            'facilityStaff.staff.user',
            'patient.user',
            'facilityStaff.facility',
            'files',
            'reschedules',
            'prescription.items.medicine',
            'review',
        ]);
    }

    public function create(array $data): Appointment
    {
        $facilityStaff = UuidResolver::model(FacilityStaff::class, $data['facility_staff_uuid']);
        $data['facility_staff_id'] = $facilityStaff->id;

        $data['patient_id'] = Patient::whereHas('user', fn ($q) => $q->where('uuid', $data['patient_uuid']))->firstOrFail()->id;

        $data['status'] = AppointmentStatus::SCHEDULED;

        $this->validateBooking(
            facilityStaffId: $data['facility_staff_id'],
            startAt: $data['start_at'],
            endAt: $data['end_at'],
        );

        return Appointment::create($data);
    }

    public function update(User $user, Appointment $appointment, array $data): Appointment
    {
        $this->authorizeAccess($user, $appointment);

        if (isset($data['start_at']) || isset($data['end_at'])) {
            $startAt = $data['start_at'] ?? $appointment->start_at->toDateTimeString();
            $endAt = $data['end_at'] ?? $appointment->end_at->toDateTimeString();

            $this->validateBooking(
                facilityStaffId: $appointment->facility_staff_id,
                startAt: $startAt,
                endAt: $endAt,
                excludeAppointmentId: $appointment->id,
            );
        }

        $appointment->update($data);

        return $appointment->refresh();
    }

    public function destroy(User $user, Appointment $appointment): void
    {
        $this->authorizeAccess($user, $appointment);
        $appointment->delete();
    }

    public function cancel(User $user, Appointment $appointment, ?string $reason = null): Appointment
    {
        $this->authorizeAccess($user, $appointment);
        $this->assertValidTransition($appointment, AppointmentStatus::CANCELLED);

        $appointment->update([
            'status' => AppointmentStatus::CANCELLED,
            'cancellation_reason' => $reason,
        ]);

        return $appointment->refresh();
    }

    public function reschedule(
        User $user,
        Appointment $appointment,
        string $newStartAt,
        string $newEndAt,
        ?string $reason = null,
    ): Appointment {
        $this->authorizeAccess($user, $appointment);
        $this->assertValidTransition($appointment, AppointmentStatus::RESCHEDULED);

        $this->validateBooking(
            facilityStaffId: $appointment->facility_staff_id,
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

    public function restore(User $user, Appointment $appointment): Appointment
    {
        $this->authorizeAccess($user, $appointment);

        $appointment->update([
            'status' => AppointmentStatus::SCHEDULED,
            'cancellation_reason' => null,
        ]);

        return $appointment->refresh();
    }

    public function forceComplete(User $user, Appointment $appointment): Appointment
    {
        $this->authorizeAccess($user, $appointment);

        $appointment->update([
            'status' => AppointmentStatus::COMPLETED,
        ]);

        return $appointment->refresh();
    }

    public function stats(): array
    {
        $allStatuses = [
            AppointmentStatus::SCHEDULED,
            AppointmentStatus::CONFIRMED,
            AppointmentStatus::CHECKED_IN,
            AppointmentStatus::IN_PROGRESS,
            AppointmentStatus::COMPLETED,
            AppointmentStatus::CANCELLED,
            AppointmentStatus::NO_SHOW,
            AppointmentStatus::RESCHEDULED,
        ];

        $counts = Appointment::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $byStatus = collect($allStatuses)->map(fn (AppointmentStatus $status) => [
            'status' => $status->value,
            'count' => (int) ($counts[$status->value] ?? 0),
        ]);

        $facilityPerformance = Facility::query()
            ->select(['facilities.id', 'facilities.uuid', 'facilities.name'])
            ->withCount([
                'appointments as appointments_count',
                'appointments as completed_count' => fn ($q) => $q
                    ->where('appointments.status', AppointmentStatus::COMPLETED),
                'appointments as cancelled_count' => fn ($q) => $q
                    ->where('appointments.status', AppointmentStatus::CANCELLED),
            ])
            ->having('appointments_count', '>', 0)
            ->get()
            ->map(fn ($facility) => [
                'facility' => [
                    'name' => $facility->name,
                ],
                'appointments_count' => (int) $facility->appointments_count,
                'completion_rate' => $facility->appointments_count > 0
                    ? round(($facility->completed_count / $facility->appointments_count) * 100, 1)
                    : 0,
                'cancellation_rate' => $facility->appointments_count > 0
                    ? round(($facility->cancelled_count / $facility->appointments_count) * 100, 1)
                    : 0,
            ])
            ->values()
            ->toArray();

        $doctorPerformance = Staff::query()
            ->select(['staff.id', 'staff.uuid'])
            ->withCount([
                'appointmentsAsDoctor as appointments_count',
                'appointmentsAsDoctor as completed_count' => fn ($q) => $q
                    ->where('appointments.status', AppointmentStatus::COMPLETED),
            ])
            ->having('appointments_count', '>', 0)
            ->get()
            ->map(fn ($doctor) => [
                'doctor' => [
                    'name' => $doctor->user?->name,
                ],
                'appointments_count' => (int) $doctor->appointments_count,
                'patients_count' => (int) $doctor->appointmentsAsDoctor()
                    ->distinct('patient_id')
                    ->count('patient_id'),
                'completion_rate' => $doctor->appointments_count > 0
                    ? round(($doctor->completed_count / $doctor->appointments_count) * 100, 1)
                    : 0,
            ])
            ->values()
            ->toArray();

        $mostActiveFacilities = Facility::query()
            ->select(['facilities.id', 'facilities.uuid', 'facilities.name'])
            ->withCount('appointments as count')
            ->having('count', '>', 0)
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($facility) => [
                'facility' => [
                    'name' => $facility->name,
                ],
                'count' => (int) $facility->count,
            ])
            ->values()
            ->toArray();

        $mostActiveDoctors = Staff::query()
            ->select(['staff.id', 'staff.uuid'])
            ->withCount('appointmentsAsDoctor as count')
            ->having('count', '>', 0)
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($doctor) => [
                'doctor' => [
                    'name' => $doctor->user?->name,
                ],
                'count' => (int) $doctor->count,
            ])
            ->values()
            ->toArray();

        return [
            'by_status' => $byStatus,
            'facility_performance' => $facilityPerformance,
            'doctor_performance' => $doctorPerformance,
            'most_active_facilities' => $mostActiveFacilities,
            'most_active_doctors' => $mostActiveDoctors,
        ];
    }

    public function analytics(): array
    {
        $total = Appointment::count();

        $scheduled = Appointment::where('status', AppointmentStatus::SCHEDULED)->count();
        $completed = Appointment::where('status', AppointmentStatus::COMPLETED)->count();
        $cancelled = Appointment::where('status', AppointmentStatus::CANCELLED)->count();
        $noShow = Appointment::where('status', AppointmentStatus::NO_SHOW)->count();

        $resolvedCount = $completed + $cancelled + $noShow;
        $completionRate = $resolvedCount > 0
            ? round(($completed / $resolvedCount) * 100, 2)
            : 0;
        $cancellationRate = $total > 0
            ? round(($cancelled / $total) * 100, 2)
            : 0;

        $activeFacilities = Facility::query()
            ->whereHas('appointments')
            ->count();

        return [
            'total_appointments' => $total,
            'scheduled' => $scheduled,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'no_show' => $noShow,
            'completion_rate' => $completionRate,
            'cancellation_rate' => $cancellationRate,
            'active_facilities' => $activeFacilities,
        ];
    }

    public function calendarAppointments(User $user, array $filters): LengthAwarePaginator
    {
        $query = Appointment::query()
            ->with([
                'facilityStaff.staff.user',
                'facilityStaff.facility',
                'patient.user',
            ]);

        if (! empty($filters['start_date'])) {
            $query->where('start_at', '>=', Carbon::parse($filters['start_date'])->startOfDay());
        }

        if (! empty($filters['end_date'])) {
            $query->where('start_at', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }

        $query = $this->applyRoleScope($query, $user);

        $facilityUuid = $filters['facility_uuid'] ?? $filters['facility'] ?? null;
        if (! empty($facilityUuid)) {
            $facilityId = UuidResolver::resolve(Facility::class, $facilityUuid);
            $query->whereIn('facility_staff_id', FacilityStaff::where('facility_id', $facilityId)->pluck('id'));
        }

        $doctorUuid = $filters['doctor_uuid'] ?? $filters['doctor'] ?? null;
        if (! empty($doctorUuid)) {
            $staffId = UuidResolver::resolve(Staff::class, $doctorUuid);
            $query->whereIn('facility_staff_id', FacilityStaff::where('staff_id', $staffId)->pluck('id'));
        }

        return $query->orderBy('start_at')
            ->paginate((int) ($filters['per_page'] ?? 200));
    }

    public function validateBooking(
        int $facilityStaffId,
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

        $schedule = StaffSchedule::query()
            ->where('facility_staff_id', $facilityStaffId)
            ->where('day_of_week', $dayOfWeek)
            ->whereTime('start_time', '<=', $start->format('H:i'))
            ->whereTime('end_time', '>=', $end->format('H:i'))
            ->first();

        if (! $schedule) {
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
            ->where('facility_staff_id', $facilityStaffId)
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

        $unavailable = StaffUnavailability::query()
            ->where('facility_staff_id', $facilityStaffId)
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

    private function applyRoleScope(Builder $query, User $user): Builder
    {
        if ($user->hasSystemRole('super_admin')) {
            return $query;
        }

        if ($user->hasSystemRole('facility_owner')) {
            $facility = Facility::where('created_by', $user->id)->first();
            if ($facility) {
                $query->whereIn('facility_staff_id', function ($q) use ($facility) {
                    $q->select('id')->from('facility_staff')->where('facility_id', $facility->id);
                });
            }

            return $query;
        }

        if ($user->staff) {
            $facilityStaffIds = $user->staff->facilityStaff()->pluck('id');

            return $query->whereIn('facility_staff_id', $facilityStaffIds);
        }

        $patient = $user->patientProfile;
        if ($patient) {
            return $query->where('patient_id', $patient->id);
        }

        $query->whereRaw('1 = 0');

        return $query;
    }

    private function authorizeAccess(User $user, Appointment $appointment): void
    {
        if ($user->hasSystemRole('super_admin')) {
            return;
        }

        if ($user->hasSystemRole('facility_owner')) {
            $facility = Facility::where('created_by', $user->id)->first();
            if ($facility && $appointment->facilityStaff->facility_id === $facility->id) {
                return;
            }
        }

        if ($user->staff) {
            $facilityStaffIds = $user->staff->facilityStaff()->pluck('id');
            if ($facilityStaffIds->contains($appointment->facility_staff_id)) {
                return;
            }
        }

        $patient = $user->patientProfile;
        if ($patient && $appointment->patient_id === $patient->id) {
            return;
        }

        abort(403, __('You do not have access to this appointment.'));
    }

    private function assertValidTransition(Appointment $appointment, AppointmentStatus $newStatus): void
    {
        $current = $appointment->status;

        if ($current === $newStatus) {
            abort(422, __('Appointment already has this status.'));
        }

        if (in_array($current, [AppointmentStatus::COMPLETED, AppointmentStatus::NO_SHOW])) {
            abort(422, __('Cannot modify a :status appointment.', ['status' => $current->value]));
        }

        if ($current === AppointmentStatus::CANCELLED && $newStatus !== AppointmentStatus::SCHEDULED) {
            abort(422, __('Cannot modify a cancelled appointment.'));
        }
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['status'])) {
            $statuses = AppointmentStatus::fromFilter($filters['status']);
            $query->whereIn('status', $statuses);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient.user', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('facilityStaff.staff.user', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('facilityStaff.facility', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $facilityUuid = $filters['facility_uuid'] ?? $filters['facility'] ?? null;
        if (! empty($facilityUuid)) {
            $facilityId = UuidResolver::resolve(Facility::class, $facilityUuid);
            $query->whereIn('facility_staff_id', FacilityStaff::where('facility_id', $facilityId)->pluck('id'));
        }

        $doctorUuid = $filters['doctor_uuid'] ?? $filters['doctor'] ?? null;
        if (! empty($doctorUuid)) {
            $staffId = UuidResolver::resolve(Staff::class, $doctorUuid);
            $query->whereIn('facility_staff_id', FacilityStaff::where('staff_id', $staffId)->pluck('id'));
        }

        if (! empty($filters['patient_uuid'])) {
            $query->whereHas('patient.user', fn ($q) => $q->where('uuid', $filters['patient_uuid']));
        }

        if (! empty($filters['facility_staff_id'])) {
            $query->where('facility_staff_id', (int) $filters['facility_staff_id']);
        }

        if (! empty($filters['patient_id'])) {
            $query->where('patient_id', (int) $filters['patient_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('start_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (! empty($filters['date_to'])) {
            $query->where('start_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        $sortField = match ($filters['sort'] ?? 'start_at') {
            'created_at' => 'created_at',
            'status' => 'status',
            default => 'start_at',
        };
        $sortOrder = ($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortOrder);

        return $query;
    }
}
