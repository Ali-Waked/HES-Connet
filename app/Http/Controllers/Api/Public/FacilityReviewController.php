<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\Facility\StoreFacilityReviewRequest;
use App\Http\Resources\FacilityReviewResource;
use App\Models\Facility;
use App\Models\FacilityReview;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FacilityReviewController extends Controller
{
    public function index(Facility $facility): AnonymousResourceCollection
    {
        $reviews = $facility->publicReviews()
            ->with('patient.user')
            ->latest()
            ->paginate(10);

        $canReview = auth()->check()
            && auth()->user()->patientProfile
            && ! $facility->publicReviews()
                ->whereHas('patient', fn ($q) => $q->where('user_id', auth()->id()))
                ->exists();

        return FacilityReviewResource::collection($reviews)
            ->additional(['can_review' => $canReview]);
    }

    public function store(StoreFacilityReviewRequest $request, Facility $facility): JsonResponse
    {
        $patient = Patient::where('user_id', $request->user()->id)->firstOrFail();

        $review = FacilityReview::updateOrCreate(
            [
                'facility_id' => $facility->id,
                'patient_id' => $patient->id,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
                'is_visible' => true,
            ],
        );

        $review->load('patient.user');

        return response()->json([
            'message' => __('Review submitted successfully.'),
            'data' => new FacilityReviewResource($review),
        ], 201);
    }
}
