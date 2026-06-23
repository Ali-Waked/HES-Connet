<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case SCHEDULED = 'scheduled';
    case CONFIRMED = 'confirmed';
    case CHECKED_IN = 'checked_in';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';
    case RESCHEDULED = 'rescheduled';

    public static function activeStatuses(): array
    {
        return [
            self::SCHEDULED,
            self::CONFIRMED,
            self::CHECKED_IN,
            self::IN_PROGRESS,
        ];
    }

    public static function finished(): array
    {
        return [
            self::COMPLETED,
            self::CANCELLED,
            self::NO_SHOW,
            self::RESCHEDULED,
        ];
    }

    // 🔥 helper: from filter string
    public static function fromFilter(string $filter): array
    {
        return match ($filter) {
            'active' => self::activeStatuses(),
            'finished' => self::finished(),
            default => [self::from($filter)],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Scheduled',
            self::CONFIRMED => 'Confirmed',
            self::CHECKED_IN => 'Checked In',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::NO_SHOW => 'No Show',
            self::RESCHEDULED => 'Rescheduled',
        };
    }

    public static function toValues(array $statuses): array
    {
        return array_map(fn ($s) => $s->value, $statuses);
    }
}
