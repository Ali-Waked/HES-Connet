<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreSubscriptionRequest;
use App\Http\Requests\Public\UpdateSubscriptionTypesRequest;
use App\Http\Resources\SubscriptionResource;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;

class PublicSubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Subscribe an email.
     */
    public function subscribe(StoreSubscriptionRequest $request): JsonResponse
    {
        $subscription = $this->subscriptionService->subscribe($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('Subscribed successfully. Please check your email to verify your subscription.'),
            'data' => new SubscriptionResource($subscription),
        ], 201);
    }

    /**
     * Verify email subscription.
     */
    public function verify(string $token): JsonResponse
    {
        info($token);
        $subscription = $this->subscriptionService->verify($token);

        return response()->json([
            'success' => true,
            'message' => __('Subscription verified successfully.'),
            'data' => new SubscriptionResource($subscription),
        ]);
    }

    /**
     * Update subscription types.
     */
    public function update(UpdateSubscriptionTypesRequest $request, string $token): JsonResponse
    {
        $subscription = $this->subscriptionService->updateTypes($token, $request->validated()['types']);

        return response()->json([
            'success' => true,
            'message' => __('Subscription updated successfully.'),
            'data' => new SubscriptionResource($subscription),
        ]);
    }

    /**
     * Unsubscribe via token.
     */
    public function unsubscribe(string $token): JsonResponse
    {
        $subscription = $this->subscriptionService->unsubscribe($token);

        return response()->json([
            'success' => true,
            'message' => __('Unsubscribed successfully.'),
            'data' => new SubscriptionResource($subscription),
        ]);
    }
}
