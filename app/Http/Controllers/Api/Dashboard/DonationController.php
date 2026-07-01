<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\DonationResource;
use App\Models\Donation;
use App\Services\DonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function __construct(
        private readonly DonationService $donationService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $donations = $this->donationService->getAllDonations(
            $request->only(['status', 'story_id', 'date_from', 'date_to', 'per_page'])
        );

        return DonationResource::collection($donations)->response();
    }

    public function show(Donation $donation): JsonResponse
    {
        return response()->json([
            'data' => DonationResource::make($donation->load(['story', 'donor.profile', 'payments'])),
        ]);
    }
}
