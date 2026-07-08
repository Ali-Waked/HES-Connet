<?php

namespace App\Enums;

enum PlatformReviewStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case HIDDEN = 'hidden';

    public function isEditable(): bool
    {
        return match ($this) {
            self::PENDING, self::REJECTED => true,
            self::APPROVED, self::HIDDEN => false,
        };
    }
}
