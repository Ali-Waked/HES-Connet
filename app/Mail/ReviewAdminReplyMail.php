<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\PlatformReview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewAdminReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PlatformReview $review,
        public string $adminReply,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('An admin responded to your review'),
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
            view: 'emails.review-admin-reply',
            with: [
                'title' => __('Admin Response to Your Review'),
                'heading' => __('We heard you!'),
                'subheading' => __('An admin has responded to your review.'),
                'userName' => $userName,
                'preMessage' => __('Here is the response from our team regarding your recent review:'),
                'adminReply' => $this->adminReply,
                'rating' => $this->review->rating,
                'reviewMessage' => $this->review->comment,
                'platform' => config('app.name'),
                'disclaimer' => __('This email was sent regarding your platform review.'),
                'supportUrl' => config('app.url').'/contact',
                'ctaUrl' => config('app.url').'/dashboard',
                'privacyUrl' => config('app.url').'/privacy',
            ],
        );
    }
}
