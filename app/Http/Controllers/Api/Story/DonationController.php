<?php

namespace App\Http\Controllers\Api\Story;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDonationRequest;
use App\Http\Resources\DonationResource;
use App\Models\Story;
use App\Services\DonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function __construct(
        private readonly DonationService $donationService,
    ) {}

    public function index(Story $story, Request $request): JsonResponse
    {
        $donations = $this->donationService->getStoryDonations(
            $story,
            $request->only(['status', 'per_page'])
        );

        return DonationResource::collection($donations)->response();
    }

    public function store(StoreDonationRequest $request): JsonResponse
    {
        $story = Story::where('uuid', $request->validated('story_id'))->firstOrFail();

        $donation = $this->donationService->createDonation(
            $story,
            $request->user()->id,
            (float) $request->validated('amount'),
            $request->validated('currency', 'SAR'),
        );

        return (new DonationResource($donation))
            ->response()
            ->setStatusCode(201);
    }

    public function stats(Story $story): JsonResponse
    {
        return response()->json([
            'data' => $this->donationService->getDonationStats($story),
        ]);
    }
}
