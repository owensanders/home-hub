<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class BudgetMonthSummaryData extends Data
{
    public function __construct(
        public string $month,
        public string $monthLabel,
        public string $total,
        public int $totalPence,
    ) {}
}
