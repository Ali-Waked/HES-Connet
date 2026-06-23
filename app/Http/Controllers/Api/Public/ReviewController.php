<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Review;

class ReviewController extends Controller
{
    public function store(Request $request,Appointment $appointment) {
        info($request->all());
        info(['adskfj',$appointment->staff_id]);
        $facility_staff = $appointment->facilityStaff;
        return Review::create(
            [
                'staff_id' => $facility_staff->staff_id,
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'rating' => $request->rating,
                'content' => $request->content,
            ]
        );
    }
}
