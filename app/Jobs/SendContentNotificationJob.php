<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\ContentPublishedMail;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendContentNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     *
     * @var int[]
     */
    public array $backoff = [30, 60, 120];

    /**
     * Create a new job instance.
     *
     * @param  Subscription  $subscription  The target subscriber.
     * @param  string  $type  Subscription type (e.g. 'article', 'job').
     * @param  string  $title  Localized content title.
     * @param  string  $body  Localized content body/excerpt.
     * @param  string|null  $contentUrl  Link to the published content (optional).
     */
    public function __construct(
        protected readonly Subscription $subscription,
        protected readonly string $type,
        protected readonly string $title,
        protected readonly string $body,
        protected readonly ?string $contentUrl,
    ) {}

    /**
     * Execute the job.
     * Sets the subscriber's locale, sends the mail, then restores the app locale.
     */
    public function handle(): void
    {
        $manageUrl = route('subscriptions.update', ['token' => $this->subscription->unsubscribe_token]);
        $unsubscribeUrl = route('subscriptions.unsubscribe', ['token' => $this->subscription->unsubscribe_token]);

        Mail::to($this->subscription->email)
            ->send(
                new ContentPublishedMail(
                    subscription: $this->subscription,
                    type: $this->type,
                    title: $this->title,
                    body: $this->body,
                    contentUrl: $this->contentUrl,
                    manageUrl: $manageUrl,
                    unsubscribeUrl: $unsubscribeUrl,
                )
            );
    }
}
