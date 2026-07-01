<?php

declare(strict_types=1);

namespace App\Notifications\Payments;

use App\Models\Payment;
use App\Notifications\BaseNotification;

class PaymentProcessedNotification extends BaseNotification
{
    public static function forAdmin(Payment $payment, ?string $locale = null): static
    {
        return new static(
            event: 'payment.processed',
            role: 'admin',
            data: [
                'amount' => number_format((float) $payment->amount, 2),
                'currency' => $payment->currency ?? 'USD',
                'status' => $payment->status?->value ?? $payment->status ?? 'processed',
                'action_text' => 'View Payment',
                'action_url' => route('dashboard.payments.show', $payment),
            ],
            locale: $locale,
        );
    }
}
