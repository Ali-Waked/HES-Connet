<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\AiMedicalConsultation;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiConsultationController extends Controller
{
    public function __construct(
        private readonly AiService $aiService,
    ) {}

    public function consult(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'patient_id' => 'required|exists:patients,id',
        ]);

        $patient = $request->user()->patientProfile;

        abort_unless($patient, 403, __('Patient profile not found.'));

        abort_if(
            (int) $patient->id !== (int) $validated['patient_id'],
            403,
            __('Unauthorized access to this patient profile.')
        );

        $response = $this->aiService->consultPatient(
            $validated,
            $request->user()->id,
        );

        AiMedicalConsultation::create([
            'patient_id' => $patient->id,
            'symptoms' => $validated['message'],
            'analysis' => $response['analysis'] ?? null,
            'urgency' => $response['urgency'] ?? 'low',
            'recommended_specialties' => $response['recommended_specialties'] ?? [],
            'recommended_doctors' => $response['recommended_doctors'] ?? [],
            'follow_up_questions' => $response['follow_up_questions'] ?? [],
        ]);

        return response()->json($response);
    }

    public function history(Request $request): JsonResponse
    {
        $patient = $request->user()->patientProfile;

        abort_unless($patient, 403, __('Patient profile not found.'));

        $consultations = AiMedicalConsultation::where('patient_id', $patient->id)
            ->latest()
            ->paginate(20);

        return response()->json($consultations);
    }
}
