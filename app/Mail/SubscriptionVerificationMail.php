<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Verification email sent to a subscriber after sign-up or token regeneration.
 * Implements ShouldQueue so it is always dispatched asynchronously.
 */
class SubscriptionVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new message instance.
     *
     * @param  Subscription  $subscription  The subscriber model.
     * @param  string  $url  The signed verification URL.
     */
    public function __construct(
        public readonly Subscription $subscription,
        public readonly string $url,
    ) {
        // Render the email in the subscriber's preferred locale.
        $this->locale($subscription->locale);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('subscriptions.verification.subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.verification',
        );
    }
}
