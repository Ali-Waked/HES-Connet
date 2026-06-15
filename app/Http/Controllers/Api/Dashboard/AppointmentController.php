<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointment_service)
    {
    }

    public function index()
    {
        return AppointmentResource::collection(
            $this->appointment_service->paginate(
                (int) request('per_page', 15),
                request('status'),
                request('staff_id') ? (int) request('staff_id') : null,
                request('patient_id') ? (int) request('patient_id') : null,
            )
        );
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $appointment = $this->appointment_service->create($request->validated());

        return response()->json([
            'message' => __('Appointment created successfully.'),
            'data' => new AppointmentResource($appointment),
        ], 201);
    }

    public function show(Appointment $appointment): JsonResponse
    {
        return response()->json([
            'data' => new AppointmentResource(
                $this->appointment_service->show($appointment)
            ),
        ]);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $appointment = $this->appointment_service->update($appointment, $request->validated());

        return response()->json([
            'message' => __('Appointment updated successfully.'),
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        $appointment->delete();

        return response()->json([
            'message' => __('Appointment deleted successfully.'),
        ]);
    }

    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $appointment = $this->appointment_service->cancel($appointment, $validated['reason'] ?? null);

        return response()->json([
            'message' => __('Appointment cancelled successfully.'),
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function reschedule(Request $request, Appointment $appointment): JsonResponse
    {
        $validated = $request->validate([
            'start_at' => ['required', 'date', 'after_or_equal:now'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $appointment = $this->appointment_service->reschedule(
            $appointment,
            $validated['start_at'],
            $validated['end_at'],
            $validated['reason'] ?? null,
        );

        return response()->json([
            'message' => __('Appointment rescheduled successfully.'),
            'data' => new AppointmentResource($appointment),
        ]);
    }
}
