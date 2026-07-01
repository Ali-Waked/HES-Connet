<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\DonationCheckoutRequest;
use App\Http\Resources\DonationResource;
use App\Http\Resources\DonationStatusResource;
use App\Models\Story;
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
        $donations = $this->donationService->getPublicDonations(
            $request->only(['per_page'])
        );

        return DonationResource::collection($donations)->response();
    }

    public function checkout(Story $story, DonationCheckoutRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->donationService->createDonationCheckout(
            $story,
            $request->user()?->id,
            (float) $validated['amount'],
            $validated['currency'] ?? 'SAR',
        );

        return response()->json(['data' => $result]);
    }

    public function status(Request $request): JsonResponse
    {
        $request->validate(['session_id' => ['required', 'string']]);

        $status = $this->donationService->getPaymentStatusBySessionId(
            $request->query('session_id'),
        );

        if (! $status) {
            return response()->json([
                'success' => false,
                'data' => ['status' => 'not_found'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => DonationStatusResource::make($status),
        ]);
    }
}
