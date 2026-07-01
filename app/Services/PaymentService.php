<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DonationStatus;
use App\Enums\PaymentStatus;
use App\Events\DonationMade;
use App\Events\InvoiceGenerated;
use App\Events\PaymentProcessed;
use App\Models\Donation;
use App\Models\Payment;
use App\Services\PaymentGateways\PaymentGatewayInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentService
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly InvoiceService $invoiceService,
    ) {}

    public function createPaymentForDonation(Donation $donation, string $provider = 'stripe'): Payment
    {
        return Payment::create([
            'donation_id' => $donation->id,
            'payable_type' => $donation->getMorphClass(),
            'payable_id' => $donation->id,
            'provider' => $provider,
            'amount' => $donation->amount,
            'currency' => $donation->currency,
            'status' => PaymentStatus::PENDING,
        ]);
    }

    public function initiateCheckout(Payment $payment, string $successUrl, string $cancelUrl): array
    {
        return $this->gateway->createCheckoutSession($payment, $successUrl, $cancelUrl);
    }

    public function handlePaymentSuccess(string $transactionRef): Payment
    {
        $payment = $this->gateway->confirmPayment($transactionRef);
        $payment->load('donation');

        if ($payment->status === PaymentStatus::PAID || $payment->status === PaymentStatus::COMPLETED) {
            if ($payment->donation) {
                $payment->donation->update([
                    'status' => DonationStatus::COMPLETED,
                    'paid_at' => now(),
                ]);
            }

            $payment->update(['paid_at' => now()]);

            $invoice = $this->invoiceService->generateForPayable($payment);
            event(new InvoiceGenerated($invoice));

            event(new PaymentProcessed($payment));

            if ($payment->donation) {
                $donorName = $payment->donation->donor?->name;
                event(new DonationMade(
                    donorName: is_array($donorName) ? ($donorName['en'] ?? 'Anonymous') : ($donorName ?? 'Anonymous'),
                    amount: (float) $payment->amount,
                    campaign: $payment->donation->story?->getTranslations('title')['en'] ?? null,
                ));
            }
        }

        return $payment;
    }

    public function markAsCompleted(Payment $payment): void
    {
        $payment->update([
            'status' => PaymentStatus::COMPLETED,
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed(Payment $payment): void
    {
        $payment->update([
            'status' => PaymentStatus::FAILED,
        ]);
    }

    public function markDonationAsCompleted(Donation $donation): void
    {
        $donation->update([
            'status' => DonationStatus::COMPLETED,
            'paid_at' => now(),
        ]);
    }

    public function markDonationAsFailed(Donation $donation): void
    {
        $donation->update([
            'status' => DonationStatus::FAILED,
        ]);
    }

    public function getAllPayments(array $filters = []): LengthAwarePaginator
    {
        return Payment::query()
            ->with(['payable', 'donation'])
            ->when(
                $filters['status'] ?? null,
                fn ($q, $v) => $q->where('status', $v)
            )
            ->when(
                $filters['provider'] ?? null,
                fn ($q, $v) => $q->where('provider', $v)
            )
            ->when(
                $filters['date_from'] ?? null,
                fn ($q, $v) => $q->whereDate('created_at', '>=', $v)
            )
            ->when(
                $filters['date_to'] ?? null,
                fn ($q, $v) => $q->whereDate('created_at', '<=', $v)
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}
