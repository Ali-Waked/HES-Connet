<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionTypeEnum: string
{
    case ARTICLE = 'article';
    case STORY = 'story';
    case JOB = 'job';
    case EVENT = 'event';
    case NEWSLETTER = 'newsletter';

    /**
     * Return all valid type values for validation rules.
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Return a comma-separated string for use in 'in:' validation rules.
     */
    public static function imploded(): string
    {
        return implode(',', self::values());
    }
}
