<?php

declare(strict_types=1);

namespace App\Enums;

enum LocaleType: string
{
    case EN = 'en';
    case AR = 'ar';

    public function label(): string
    {
        return match ($this) {
            self::EN => 'English',
            self::AR => 'العربية',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $locale) => [
                'value' => $locale->value,
                'label' => $locale->label(),
            ])
            ->toArray();
    }
}
