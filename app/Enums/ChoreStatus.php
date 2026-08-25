<?php

declare(strict_types=1);

namespace App\Enums;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/** The three columns of the chore board. */
enum ChoreStatus: string
{
    case Today = 'today';
    case Upcoming = 'upcoming';
    case Done = 'done';

    /** Which column a chore lands in based on its due date. */
    public static function fromDueDate(CarbonInterface $date): self
    {
        return $date->copy()->startOfDay()->gt(Carbon::today()) ? self::Upcoming : self::Today;
    }

    public function label(): string
    {
        return match ($this) {
            self::Today => "Today's tasks",
            self::Upcoming => 'Upcoming',
            self::Done => 'Completed',
        };
    }
}
