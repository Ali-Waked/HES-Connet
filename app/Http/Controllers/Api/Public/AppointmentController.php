<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PublicAppointmentResource;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointmentService
    ) {}

    public function index(Request $request): JsonResponse
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

        if ($request->filled('status')) {
            $query->whereIn('status', AppointmentStatus::fromFilter($request->status));
        }

        return response()->json(
            PublicAppointmentResource::collection(
                $query->latest('start_at')->paginate(20)
            )
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_uuid' => ['required', 'exists:facilities,uuid'],
            'doctor_uuid' => ['required', 'exists:staff,uuid'],
            'start_at' => ['required', 'date', 'after:now'],
            'reason' => ['nullable', 'string'],
        ]);

        $appointment = $this->appointmentService->bookForPatient($validated, auth()->user());

        return response()->json([
            'message' => __('Appointment booked successfully.'),
            'data' => new PublicAppointmentResource($appointment),
        ], 201);
    }
}
