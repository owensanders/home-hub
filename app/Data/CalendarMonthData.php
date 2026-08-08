<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class CalendarMonthData extends Data
{
    /**
     * @param  list<string>  $weekdayLabels
     * @param  list<CalendarDayData>  $days
     * @param  list<MemberData>  $members
     */
    public function __construct(
        public string $month,
        public string $monthLabel,
        public string $previousMonth,
        public string $nextMonth,
        public string $today,
        public array $weekdayLabels,
        public array $days,
        public array $members,
    ) {}
}
