<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The four columns of the chore board.
 */
enum ChoreStatus: string
{
    case Today = 'today';
    case Upcoming = 'upcoming';
    case Done = 'done';
    case Recurring = 'recurring';

    public function label(): string
    {
        return match ($this) {
            self::Today => "Today's tasks",
            self::Upcoming => 'Upcoming',
            self::Done => 'Completed',
            self::Recurring => 'Recurring',
        };
    }
}
