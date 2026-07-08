<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $contactMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('We received your message'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'lang' => 'en',
                'dir' => 'ltr',
                'name' => $this->contactMessage->name,
                'title' => __('Thank you for contacting us'),
                'body' => __('We have received your message and will get back to you shortly.'),
                'actionText' => null,
                'actionUrl' => null,
            ],
        );
    }
}
