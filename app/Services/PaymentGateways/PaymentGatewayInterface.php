<?php

namespace App\Services\PaymentGateways;

use App\Models\Payment;

interface PaymentGatewayInterface
{
    public function createCheckoutSession(Payment $payment, string $successUrl, string $cancelUrl): array;

    public function confirmPayment(string $transactionRef): Payment;

    public function processWebhook(array $payload): void;
}
