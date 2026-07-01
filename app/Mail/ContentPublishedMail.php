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
 * Notification email dispatched to a verified subscriber when new content is published.
 * Implements ShouldQueue so every send is always asynchronous.
 */
class ContentPublishedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Back-off in seconds between retries.
     *
     * @var int[]
     */
    public array $backoff = [30, 60, 120];

    /**
     * Create a new message instance.
     *
     * @param  Subscription  $subscription  The subscriber receiving the email.
     * @param  string  $type  Content type (e.g. 'article', 'job').
     * @param  string  $title  Localized title of the published content.
     * @param  string  $body  Localized body/excerpt of the published content.
     * @param  string|null  $contentUrl  Link to the full content (optional).
     * @param  string  $manageUrl  Link to manage subscription types.
     * @param  string  $unsubscribeUrl  One-click unsubscribe link.
     */
    public function __construct(
        public readonly Subscription $subscription,
        public readonly string $type,
        public readonly string $title,
        public readonly string $body,
        public readonly ?string $contentUrl,
        public readonly string $manageUrl,
        public readonly string $unsubscribeUrl,
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
            subject: __('subscriptions.notifications.subject_new_content', [
                'type' => ucfirst($this->type),
            ]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.content_published',
            with: [
                'type' => $this->type,
                'title' => $this->title,
                'body' => $this->body,
                'contentUrl' => $this->contentUrl,
                'manageUrl' => $this->manageUrl,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ],
        );
    }
}
