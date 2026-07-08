<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\ContactMessage;

class ContactMessageSubmittedNotification extends BaseNotification
{
    public static function forAdmin(ContactMessage $contactMessage, ?string $locale = null): static
    {
        return new static(
            event: 'contact.submitted',
            role: 'admin',
            data: [
                'name' => $contactMessage->name,
                'email' => $contactMessage->email,
                'action_text' => 'View Message',
                'action_url' => route('admin.contact-messages.show', $contactMessage),
            ],
            locale: $locale,
        );
    }
}
