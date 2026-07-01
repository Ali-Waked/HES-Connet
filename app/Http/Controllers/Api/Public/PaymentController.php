<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StripeCheckoutRequest;
use App\Models\Donation;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    public function createStripeCheckout(StripeCheckoutRequest $request): JsonResponse
    {
        $donation = Donation::where('uuid', $request->validated('donation_id'))->firstOrFail();

        $payment = $this->paymentService->createPaymentForDonation($donation);

        $result = $this->paymentService->initiateCheckout(
            $payment,
            $request->validated('success_url'),
            $request->validated('cancel_url'),
        );

        return response()->json(['data' => $result]);
    }
}
