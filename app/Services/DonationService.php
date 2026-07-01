<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DonationStatus;
use App\Enums\PaymentStatus;
use App\Events\DonationCreated;
use App\Models\Donation;
use App\Models\Payment;
use App\Models\Story;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DonationService
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly StripePaymentService $stripePaymentService,
    ) {}

    public function createDonation(
        Story $story,
        ?int $donorId,
        float $amount,
        string $currency = 'SAR',
    ): Donation {
        $donation = Donation::create([
            'story_id' => $story->id,
            'donor_id' => $donorId,
            'amount' => $amount,
            'currency' => $currency,
            'status' => DonationStatus::PENDING,
        ]);

        event(new DonationCreated($donation));

        return $donation;
    }

    public function createDonationCheckout(
        Story $story,
        ?int $donorId,
        float $amount,
        string $currency = 'SAR',
    ): array {
        $frontendUrl = config('app.frontend_url');

        $successUrl = $frontendUrl.'/donation/success?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = $frontendUrl.'/donation/cancel';

        return DB::transaction(function () use ($story, $donorId, $amount, $currency, $successUrl, $cancelUrl) {
            $donation = $this->createDonation($story, $donorId, $amount, $currency);

            $payment = $this->paymentService->createPaymentForDonation($donation, 'stripe');

            $session = $this->stripePaymentService->createCheckoutSession(
                $donation,
                $payment,
                $successUrl,
                $cancelUrl,
            );

            $payment->update([
                'transaction_ref' => $session->id,
                'provider_payment_id' => $session->id,
                'response_json' => $session->toArray(),
                'status' => PaymentStatus::PROCESSING,
            ]);

            $donation->update(['status' => DonationStatus::PROCESSING]);

            return [
                'checkout_url' => $session->url,
                'session_id' => $session->id,
                'donation_id' => $donation->uuid,
                'payment_id' => $payment->uuid,
            ];
        });
    }

    public function getStoryDonations(Story $story, array $filters = []): LengthAwarePaginator
    {
        return Donation::where('story_id', $story->id)
            ->with(['donor.profile', 'payments'])
            ->when(
                $filters['status'] ?? null,
                fn ($q, $v) => $q->where('status', $v)
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function getDonationStats(Story $story): array
    {
        $stats = Donation::where('story_id', $story->id)
            ->selectRaw("
                COUNT(*) as total_donations,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END), 0) as total_collected,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count
            ")
            ->first();

        return [
            'total_donations' => (int) $stats->total_donations,
            'total_collected' => (float) $stats->total_collected,
            'completed_count' => (int) $stats->completed_count,
        ];
    }

    public function getPaymentStatusBySessionId(string $sessionId): ?array
    {
        $payment = Payment::where('transaction_ref', $sessionId)
            ->orWhere('provider_payment_id', $sessionId)
            ->first();

        if (! $payment) {
            return null;
        }

        $donation = $payment->donation;

        return [
            'status' => $donation?->status?->value ?? $payment->status->value,
            'donation_status' => $donation?->status?->value,
            'payment_status' => $payment->status->value,
            'paid_at' => $payment->paid_at?->toIso8601String(),
        ];
    }

    public function getPublicDonations(array $filters = []): LengthAwarePaginator
    {
        return Donation::where('status', DonationStatus::COMPLETED)
            ->with(['story', 'donor.profile'])
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function getAllDonations(array $filters = []): LengthAwarePaginator
    {
        return Donation::query()
            ->with(['story', 'donor.profile', 'payments'])
            ->when(
                $filters['status'] ?? null,
                fn ($q, $v) => $q->where('status', $v)
            )
            ->when(
                $filters['story_id'] ?? null,
                fn ($q, $v) => $q->where('story_id', $v)
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
