<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\CancelAppointmentRequest;
use App\Http\Requests\Appointment\RescheduleAppointmentRequest;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentRequest;
use App\Http\Resources\Dashboard\AppointmentResource;
use App\Http\Resources\Dashboard\AppointmentCalendarResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointmentService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            AppointmentResource::collection(
                $this->appointmentService->paginate(
                    $request->user(),
                    $request->only([
                        'search', 'status', 'facility_uuid', 'doctor_uuid', 'patient_uuid',
                        'facility_staff_id', 'patient_id',
                        'date_from', 'date_to', 'sort', 'sort_order', 'per_page',
                        'facility', 'doctor',
                    ])
                )
            )
        );
    }

    public function show(Request $request, Appointment $appointment): JsonResponse
    {
        return response()->json([
            'data' => new AppointmentResource(
                $this->appointmentService->show($request->user(), $appointment)
            ),
        ]);
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $appointment = $this->appointmentService->create($request->validated());

        return response()->json([
            'message' => __('Appointment created successfully.'),
            'data' => new AppointmentResource($appointment),
        ], 201);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $appointment = $this->appointmentService->update(
            $request->user(),
            $appointment,
            $request->validated()
        );

        return response()->json([
            'message' => __('Appointment updated successfully.'),
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function destroy(Request $request, Appointment $appointment): JsonResponse
    {
        $this->appointmentService->destroy($request->user(), $appointment);

        return response()->json([
            'message' => __('Appointment deleted successfully.'),
        ]);
    }

    public function cancel(CancelAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $appointment = $this->appointmentService->cancel(
            $request->user(),
            $appointment,
            $request->validated('reason')
        );

        return response()->json([
            'message' => __('Appointment cancelled successfully.'),
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function reschedule(RescheduleAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $appointment = $this->appointmentService->reschedule(
            $request->user(),
            $appointment,
            $request->validated('start_at'),
            $request->validated('end_at'),
            $request->validated('reason'),
        );

        return response()->json([
            'message' => __('Appointment rescheduled successfully.'),
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function restore(Request $request, Appointment $appointment): JsonResponse
    {
        $appointment = $this->appointmentService->restore($request->user(), $appointment);

        return response()->json([
            'message' => __('Appointment restored successfully.'),
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function forceComplete(Request $request, Appointment $appointment): JsonResponse
    {
        $appointment = $this->appointmentService->forceComplete($request->user(), $appointment);

        return response()->json([
            'message' => __('Appointment marked as completed.'),
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'data' => $this->appointmentService->stats(),
        ]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_uuid' => ['nullable', 'exists:facilities,uuid'],
            'doctor_uuid' => ['nullable', 'exists:staff,uuid'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        return response()->json(
            AppointmentCalendarResource::collection(
                $this->appointmentService->calendarAppointments(
                    $request->user(),
                    $validated
                )
            )
        );
    }

    public function analytics(): JsonResponse
    {
        return response()->json([
            'data' => $this->appointmentService->analytics(),
        ]);
    }
}
