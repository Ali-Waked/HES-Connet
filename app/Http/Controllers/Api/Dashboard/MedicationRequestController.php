<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\MedicationRequestResource;
use App\Models\MedicationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicationRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $requests = MedicationRequest::query()
            ->with(['patient.user', 'facility', 'prescription', 'pharmacist.user'])
            ->when(
                $request->get('status'),
                fn ($q, $status) => $q->where('status', $status)
            )
            ->latest()
            ->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'data' => MedicationRequestResource::collection($requests),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ]);
    }
}
