<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DonationStatus;
use App\Enums\PaymentStatus;
use App\Events\DonationCompleted;
use App\Models\Donation;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCheckoutSession(Donation $donation, Payment $payment, string $successUrl, string $cancelUrl): Session
    {
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($donation->currency),
                    'unit_amount' => (int) ($donation->amount * 100),
                    'product_data' => [
                        'name' => 'Donation to '.($donation->story?->getTranslations('title')['en'] ?? 'Healthcare Story'),
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'donation_id' => $donation->uuid,
                'payment_id' => $payment->uuid,
            ],
        ]);

        return $session;
    }

    public function verifyWebhookSignature(Request $request): Event
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            return Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function handleSuccessEvent(Session $session): void
    {
        $metadata = $session->metadata;
        $paymentUuid = $metadata['payment_id'] ?? null;

        if (! $paymentUuid) {
            Log::error('Stripe webhook: missing payment_id in session metadata', [
                'session_id' => $session->id,
            ]);

            return;
        }

        $payment = Payment::where('uuid', $paymentUuid)->first();

        if (! $payment) {
            Log::error('Stripe webhook: payment not found for uuid', [
                'payment_uuid' => $paymentUuid,
                'session_id' => $session->id,
            ]);

            return;
        }

        if ($payment->status === PaymentStatus::COMPLETED || $payment->status === PaymentStatus::PAID) {
            Log::info('Stripe webhook: payment already processed, skipping', [
                'payment_uuid' => $paymentUuid,
                'session_id' => $session->id,
            ]);

            return;
        }

        $paymentIntentId = $session->payment_intent;
        if ($paymentIntentId instanceof PaymentIntent) {
            $paymentIntentId = $paymentIntentId->id;
        }

        $donation = null;

        DB::transaction(function () use ($payment, $session, $paymentIntentId, &$donation) {
            $payment->update([
                'status' => PaymentStatus::COMPLETED,
                'provider_payment_id' => $session->id,
                'transaction_ref' => $session->id,
                'stripe_payment_intent' => $paymentIntentId,
                'response_json' => $session->toArray(),
                'paid_at' => now(),
            ]);

            $donation = $payment->donation;
            if ($donation) {
                $donation->update([
                    'status' => DonationStatus::COMPLETED,
                    'paid_at' => now(),
                ]);
            }
        });

        if ($donation) {
            DonationCompleted::dispatch($donation);
        }

        Log::info('Stripe payment completed successfully', [
            'payment_uuid' => $paymentUuid,
            'session_id' => $session->id,
            'donation_id' => $payment->donation_id,
            'payment_intent' => $paymentIntentId,
        ]);
    }

    public function handleFailedEvent(Session $session): void
    {
        $metadata = $session->metadata;
        $paymentUuid = $metadata['payment_id'] ?? null;

        if (! $paymentUuid) {
            Log::error('Stripe webhook: missing payment_id in expired session metadata', [
                'session_id' => $session->id,
            ]);

            return;
        }

        $payment = Payment::where('uuid', $paymentUuid)->first();

        if (! $payment) {
            Log::error('Stripe webhook: payment not found for expired session', [
                'payment_uuid' => $paymentUuid,
                'session_id' => $session->id,
            ]);

            return;
        }

        if ($payment->status === PaymentStatus::FAILED) {
            Log::info('Stripe webhook: payment already marked as failed, skipping', [
                'payment_uuid' => $paymentUuid,
            ]);

            return;
        }

        DB::transaction(function () use ($payment, $session) {
            $payment->update([
                'status' => PaymentStatus::FAILED,
                'response_json' => $session->toArray(),
            ]);

            $donation = $payment->donation;
            if ($donation && $donation->status !== DonationStatus::COMPLETED) {
                $donation->update([
                    'status' => DonationStatus::FAILED,
                ]);
            }
        });

        Log::info('Stripe payment failed/expired', [
            'payment_uuid' => $paymentUuid,
            'session_id' => $session->id,
        ]);
    }
}
