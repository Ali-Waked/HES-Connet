<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrescriptionCollection;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index(Request $request): PrescriptionCollection
    {
        $prescriptions = Prescription::query()
            ->with(['appointment.facilityStaff.staff.user', 'items.medicine', 'appointment.patient.user'])
            ->latest()
            ->paginate((int) $request->get('per_page', 15));
        info($prescriptions);

        return new PrescriptionCollection($prescriptions);
    }
}
