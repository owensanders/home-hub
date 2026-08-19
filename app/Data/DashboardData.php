<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class DashboardData extends Data
{
    /**
     * @param  list<MemberData>  $family
     * @param  list<ChoreData>  $chores
     * @param  list<AgendaGroupData>  $agenda
     */
    public function __construct(
        public string $greeting,
        public string $dateLine,
        public string $summaryLine,
        public int $streakDays,
        public array $family,
        public ?PlannedMealData $tonight,
        public ?ShoppingListData $shoppingList,
        public array $chores,
        public ChoreProgressData $choreProgress,
        public array $agenda,
        public BudgetSummaryData $budget,
    ) {}
}
