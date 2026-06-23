<?php

namespace App\Http\Controllers\Api\Facility;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityAppointmentResource;
use App\Models\Appointment;
use App\Models\Facility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request, Facility $facility)
    {
        $facilityStaff = auth()->user()?->staff?->facilityStaff()
            ->where('facility_id', $facility->id)
            ->first();

        if (! $facilityStaff) {
            return response()->json([
                'message' => 'Unauthorized facility access.',
            ], 403);
        }

        $query = $facility->appointments()
            ->with([
                'patient.user',
                'prescription',
                'facilityStaff.staff.user',
                'facilityStaff.facility',
            ]);

        if ($facilityStaff->role->slug !== 'facility_owner') {
            $query->where('facility_staff_id', $facilityStaff->id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('facility_staff_id')) {
            $query->where('facility_staff_id', $request->facility_staff_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('scheduled_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('scheduled_at', '<=', $request->to);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('patient.user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('facilityStaff.staff.user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $appointments = $query
            ->latest()
            ->paginate($request->get('per_page', 15));

        return FacilityAppointmentResource::collection($appointments);
    }

    public function show(Facility $facility, Appointment $appointment)
    {
        $facilityStaff = auth()->user()?->staff?->facilityStaff()
            ->where('facility_id', $facility->id)
            ->first();

        if (! $facilityStaff) {
            return response()->json([
                'message' => 'Unauthorized facility access.',
            ], 403);
        }

    }

    public function stats(Facility $facility): JsonResponse
    {
        $facilityStaff = auth()->user()?->staff?->facilityStaff()
            ->where('facility_id', $facility->id)
            ->first();

        if (! $facilityStaff) {
            return response()->json([
                'message' => 'Unauthorized facility access.',
            ], 403);
        }

        $baseQuery = $facilityStaff->role->slug === 'facility_owner'
            ? $facility->appointments()
            : $facilityStaff->appointments();

        $clone = fn () => clone $baseQuery;

        return response()->json([
            'total' => $clone()->count(),

            'today' => $clone()
                ->whereDate('start_at', today())
                ->count(),

            'upcoming' => $clone()
                ->where('start_at', '>=', now())
                ->whereNotIn('status', [
                    AppointmentStatus::CANCELLED->value,
                    AppointmentStatus::NO_SHOW->value,
                    AppointmentStatus::COMPLETED->value,
                ])
                ->count(),

            'completed' => $clone()
                ->where('status', AppointmentStatus::COMPLETED)
                ->count(),

            'cancelled' => $clone()
                ->where('status', AppointmentStatus::CANCELLED)
                ->count(),

            'no_show' => $clone()
                ->where('status', AppointmentStatus::NO_SHOW)
                ->count(),

            'rescheduled' => $clone()
                ->where('status', AppointmentStatus::RESCHEDULED)
                ->count(),
        ]);
    }

    public function lookup(Request $request, Facility $facility): JsonResponse
    {
        $facilityStaff = auth()->user()?->staff?->facilityStaff()
            ->where('facility_id', $facility->id)
            ->first();

        if (! $facilityStaff) {
            return response()->json([
                'message' => 'Unauthorized facility access.',
            ], 403);
        }

        $search = $request->string('search')->toString();
        // $this->ensureFacilityAccess($staff, $facility);

        $appointments = Appointment::query()
            ->where('facility_staff_id', $facilityStaff->id)
            ->whereIn('status', AppointmentStatus::activeStatuses())

            // مهم جدًا: ما يكون لها prescription
            ->whereDoesntHave('prescription')

            // optional search
            ->when($search, function ($q) use ($search) {
                $q->whereHas('patient.user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            })
            ->with([
                'patient.user:id,uuid,name,avatar',
                'facilityStaff.staff.user:id,uuid,name,avatar',
            ])

            ->latest()
            ->limit(20)
            ->get()
            ->append('label');

        return response()->json([
            'data' => $appointments,
        ]);
    }
}
