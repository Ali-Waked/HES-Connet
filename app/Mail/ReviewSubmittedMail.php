<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\PlatformReview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PlatformReview $review,
        public array $messages,
        public string $heading,
        public ?string $rtlMessage = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->heading,
        );
    }

    public function content(): Content
    {
        $userName = $this->review->user->name;
        $locale = $this->review->user->locale ?? 'en';

        if (is_array($userName)) {
            $userName = $userName[$locale] ?? ($userName['en'] ?? 'User');
        }

        return new Content(
            view: 'emails.review-submitted',
            with: [
                'title' => $this->heading,
                'heading' => $this->heading,
                'subheading' => __('Thank you for sharing your experience.'),
                'userName' => $userName,
                'messages' => $this->messages,
                'rtlMessage' => $this->rtlMessage,
                'platform' => config('app.name'),
                'disclaimer' => __('This email was sent as part of your platform review.'),
                'supportUrl' => config('app.url').'/contact',
                'ctaUrl' => config('app.url').'/dashboard',
                'privacyUrl' => config('app.url').'/privacy',
            ],
        );
    }
}
