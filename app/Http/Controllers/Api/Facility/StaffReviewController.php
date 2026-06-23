<?php

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Http\Resources\StaffReviewResource;
use App\Models\Review;
use App\Models\ReviewReply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffReviewController extends Controller
{
    public function index(Request $request)
    {
        $staff = auth()->user()->staff;

        $reviews = Review::query()
            ->where('staff_id', $staff->id)

            ->with([
                'patient:id,user_id',
                'patient.user:id,name',
                'reply',
                'appointment:id,uuid,start_at,end_at',
            ])

            // Search by patient name
            ->when(
                $request->filled('patient_name'),
                fn ($query) => $query->whereHas(
                    'patient.user',
                    fn ($q) => $q->where(
                        'name',
                        'like',
                        '%'.$request->patient_name.'%'
                    )
                )
            )

            // Filter by rating
            ->when(
                $request->filled('rating'),
                fn ($query) => $query->where(
                    'rating',
                    $request->integer('rating')
                )
            )

            // replied | waiting
            ->when(
                $request->status === 'replied',
                fn ($query) => $query->has('reply')
            )

            ->when(
                $request->status === 'waiting',
                fn ($query) => $query->doesntHave('reply')
            )

            ->latest()
            ->paginate(
                $request->integer('per_page', 15)
            );

        return StaffReviewResource::collection($reviews);
    }

    public function reply(Request $request, Review $review): JsonResponse
    {
        $staff = auth()->user()->staff;

        abort_if(
            $review->staff_id !== $staff->id,
            403
        );

        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:5000'],
        ]);

        $reply = ReviewReply::updateOrCreate(
            [
                'review_id' => $review->id,
            ],
            [
                'reply' => $validated['reply'],
            ]
        );

        return response()->json([
            'message' => 'Reply saved successfully.',
            'data' => $reply,
        ]);
    }
}
