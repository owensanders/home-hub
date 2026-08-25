<?php

declare(strict_types=1);

namespace App\Enums;

enum ChoreRepeat: string
{
    case None = 'none';
    case Daily = 'daily';
    case Weekdays = 'weekdays';
    case Weekly = 'weekly';
    case Fortnightly = 'fortnightly';
    case Monthly = 'monthly';

    /** Option text for the repeat <select>. */
    public function label(): string
    {
        return match ($this) {
            self::None => 'Does not repeat',
            self::Daily => 'Every day',
            self::Weekdays => 'Every weekday',
            self::Weekly => 'Every week',
            self::Fortnightly => 'Every 2 weeks',
            self::Monthly => 'Every month',
        };
    }

    /** Short badge shown on a chore card, or null when it doesn't repeat. */
    public function chip(): ?string
    {
        return match ($this) {
            self::None => null,
            self::Daily => 'Daily',
            self::Weekdays => 'Weekdays',
            self::Weekly => 'Weekly',
            self::Fortnightly => 'Fortnightly',
            self::Monthly => 'Monthly',
        };
    }
}
