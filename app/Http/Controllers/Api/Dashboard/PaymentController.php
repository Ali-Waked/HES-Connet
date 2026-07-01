<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $payments = $this->paymentService->getAllPayments(
            $request->only(['status', 'provider', 'date_from', 'date_to', 'per_page'])
        );

        return PaymentResource::collection($payments)->response();
    }
}
