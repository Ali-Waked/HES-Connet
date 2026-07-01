<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stripe\Event;

class WebhookService
{
    private const IDEMPOTENCY_TTL_SECONDS = 86400;

    public function __construct(
        private readonly StripePaymentService $stripePaymentService,
    ) {}

    public function handleStripeWebhook(Request $request): JsonResponse
    {
        try {
            $event = $this->stripePaymentService->verifyWebhookSignature($request);
        } catch (\Exception $e) {
            Log::error('Stripe webhook: invalid signature', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        if ($this->isAlreadyProcessed($event->id)) {
            Log::info('Stripe webhook: event already processed, skipping', [
                'event_id' => $event->id,
                'type' => $event->type,
            ]);

            return response()->json(['status' => 'already_processed']);
        }

        Log::info('Stripe webhook received', [
            'event_id' => $event->id,
            'type' => $event->type,
        ]);

        try {
            match ($event->type) {
                'checkout.session.completed' => $this->handleCheckoutCompleted($event),
                'checkout.session.expired' => $this->handleCheckoutExpired($event),
                default => Log::info('Stripe webhook: unhandled event type', [
                    'type' => $event->type,
                    'event_id' => $event->id,
                ]),
            };
        } catch (\Exception $e) {
            Log::error('Stripe webhook: processing failed', [
                'event_id' => $event->id,
                'type' => $event->type,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }

        $this->markAsProcessed($event->id);

        return response()->json(['status' => 'ok']);
    }

    private function handleCheckoutCompleted(Event $event): void
    {
        $session = $event->data->object;
        $this->stripePaymentService->handleSuccessEvent($session);
    }

    private function handleCheckoutExpired(Event $event): void
    {
        $session = $event->data->object;
        $this->stripePaymentService->handleFailedEvent($session);
    }

    private function isAlreadyProcessed(string $eventId): bool
    {
        return Cache::has('stripe_webhook_'.$eventId);
    }

    private function markAsProcessed(string $eventId): void
    {
        Cache::put('stripe_webhook_'.$eventId, true, self::IDEMPOTENCY_TTL_SECONDS);
    }
}
