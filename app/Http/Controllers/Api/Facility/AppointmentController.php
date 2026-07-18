<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityAppointmentResource;
use App\Models\Appointment;
use App\Models\Facility;
use App\Models\FacilityStaff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request, Facility $facility): JsonResponse
    {
        $facilityStaff = $this->resolveFacilityStaff($facility);

        if (! $facilityStaff) {
            return $this->unauthorized();
        }

        $query = $this->baseQuery($facility, $facilityStaff)
            ->with([
                'patient.user',
                'prescription',
                'facilityStaff.staff.user',
                'facilityStaff.facility',
            ]);

        // role-based restriction
        if (! $facilityStaff->is_owner) {
            $query->where('facility_staff_id', $facilityStaff->id);
        }

        $query
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status)
            )
            ->when($request->filled('facility_staff_id'), fn ($q) => $q->where('facility_staff_id', $request->facility_staff_id)
            )
            ->when($request->filled('from'), fn ($q) => $q->whereDate('start_at', '>=', $request->from)
            )
            ->when($request->filled('to'), fn ($q) => $q->whereDate('start_at', '<=', $request->to)
            )
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($sub) use ($search) {
                    $sub->whereHas('patient.user', fn ($q2) => $q2->where('name', 'like', "%{$search}%")
                    )
                        ->orWhereHas('facilityStaff.staff.user', fn ($q2) => $q2->where('name', 'like', "%{$search}%")
                        );
                });
            });

        return response()->json(
            FacilityAppointmentResource::collection(
                $query->latest()->paginate($request->integer('per_page', 15))
            )
        );
    }

    public function show(Facility $facility, Appointment $appointment): JsonResponse
    {
        if (! $this->resolveFacilityStaff($facility)) {
            return $this->unauthorized();
        }

        return response()->json(
            new FacilityAppointmentResource($appointment)
        );
    }

    public function stats(Facility $facility): JsonResponse
    {
        $facilityStaff = $this->resolveFacilityStaff($facility);

        if (! $facilityStaff) {
            return $this->unauthorized();
        }

        $base = $this->baseQuery($facility, $facilityStaff);

        $clone = fn () => clone $base;

        return response()->json([
            'total' => $clone()->count(),
            'today' => $clone()->whereDate('start_at', today())->count(),
            'upcoming' => $clone()
                ->where('start_at', '>=', now())
                ->whereNotIn('status', AppointmentStatus::finished())
                ->count(),
            'completed' => $clone()->where('status', AppointmentStatus::COMPLETED)->count(),
            'cancelled' => $clone()->where('status', AppointmentStatus::CANCELLED)->count(),
            'no_show' => $clone()->where('status', AppointmentStatus::NO_SHOW)->count(),
            'rescheduled' => $clone()->where('status', AppointmentStatus::RESCHEDULED)->count(),
        ]);
    }

    public function lookup(Request $request, Facility $facility): JsonResponse
    {
        $facilityStaff = $this->resolveFacilityStaff($facility);

        if (! $facilityStaff) {
            return $this->unauthorized();
        }

        $search = $request->string('search')->toString();

        $appointments = Appointment::query()
            ->where('facility_staff_id', $facilityStaff->id)
            ->whereIn('status', AppointmentStatus::activeStatuses())
            ->whereDoesntHave('prescription')
            ->when($search, fn ($q) => $q->whereHas('patient.user', fn ($q2) => $q2->where('name', 'like', "%{$search}%")
            )
            )
            ->with([
                'patient.user:id,uuid,name,avatar',
                'facilityStaff.staff.user:id,uuid,name,avatar',
            ])
            ->latest()
            ->limit(20)
            ->get()
            ->append('label');

        return response()->json(['data' => $appointments]);
    }

    private function baseQuery(Facility $facility, FacilityStaff $staff)
    {
        return $staff->is_owner
            ? $facility->appointments()
            : $staff->appointments();
    }

    private function resolveFacilityStaff(Facility $facility): ?FacilityStaff
    {
        return auth()->user()?->staff?->facilityStaff()
            ->where('facility_id', $facility->id)
            ->first();
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json(['message' => 'Unauthorized facility access.'], 403);
    }
}
