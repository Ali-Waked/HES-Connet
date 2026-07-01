<?php

namespace App\Services\PaymentGateways;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeGateway implements PaymentGatewayInterface
{
    private readonly StripeClient $stripe;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createCheckoutSession(Payment $payment, string $successUrl, string $cancelUrl): array
    {
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($payment->currency),
                    'unit_amount' => (int) ($payment->amount * 100),
                    'product_data' => [
                        'name' => 'Donation',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'payment_uuid' => $payment->uuid,
            ],
        ]);

        $payment->update([
            'transaction_ref' => $session->id,
            'response_json' => $session->toArray(),
        ]);

        return [
            'session_id' => $session->id,
            'url' => $session->url,
        ];
    }

    public function confirmPayment(string $transactionRef): Payment
    {
        $session = $this->stripe->checkout->sessions->retrieve($transactionRef);

        $payment = Payment::where('transaction_ref', $transactionRef)->firstOrFail();

        $status = match ($session->payment_status) {
            'paid' => PaymentStatus::PAID,
            'unpaid' => PaymentStatus::PENDING,
            'no_payment_required' => PaymentStatus::PAID,
            default => PaymentStatus::FAILED,
        };

        $payment->update([
            'status' => $status,
            'response_json' => $session->toArray(),
        ]);

        return $payment->fresh();
    }

    public function processWebhook(array $payload): void
    {
        $type = $payload['type'] ?? '';

        if ($type === 'checkout.session.completed') {
            $session = $payload['data']['object'];
            $this->confirmPayment($session['id']);
        }
    }
}
