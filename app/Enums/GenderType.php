<?php

declare(strict_types=1);

namespace App\Enums;

enum GenderType: string
{
    case MALE = 'male';
    case FEMALE = 'female';

    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
