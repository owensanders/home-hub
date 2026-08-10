<?php

declare(strict_types=1);

namespace App\UseCases\Budget;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Data\BudgetCategoryData;
use App\Data\IncomeSourceData;
use App\Models\BudgetCategory;
use App\Models\Household;
use App\Models\IncomeSource;
use App\Support\Money;
use Carbon\CarbonImmutable;

class GetBudgetScreenUseCase
{
    public function __construct(private readonly BudgetRepositoryInterface $budget) {}

    /**
     * @return array{
     *     month: string, monthLabel: string, previousMonth: string, nextMonth: string,
     *     daysLeftLabel: string, leftToSpend: string, budgetedLabel: string, perDay: string,
     *     budgetedPence: int, incomeTotalPence: int,
     *     income: list<IncomeSourceData>, incomeTotal: string,
     *     categories: list<BudgetCategoryData>,
     * }
     */
    public function execute(Household $household, CarbonImmutable $month): array
    {
        $today = CarbonImmutable::today();
        $first = $month->startOfMonth();

        $categories = $this->budget->categoriesFor($household, $first);
        $income = $this->budget->incomeSourcesFor($household);

        $budgeted = (int) $categories->sum('budgeted_pence');
        $incomeTotal = (int) $income->sum('amount_pence');
        $leftToSpend = $incomeTotal - $budgeted;

        $daysLeft = $first->isSameMonth($today)
            ? (int) $today->diffInDays($first->endOfMonth(), absolute: true)
            : $first->daysInMonth;

        return [
            'month' => $first->format('Y-m'),
            'monthLabel' => $first->format('F Y'),
            'previousMonth' => $first->subMonth()->format('Y-m'),
            'nextMonth' => $first->addMonth()->format('Y-m'),
            'daysLeftLabel' => $daysLeft.' '.($daysLeft === 1 ? 'day' : 'days').' left',
            'leftToSpend' => Money::format($leftToSpend),
            'budgetedLabel' => Money::format($budgeted),
            'perDay' => Money::format($daysLeft > 0 ? intdiv(max($leftToSpend, 0), $daysLeft) : 0),
            'budgetedPence' => $budgeted,
            'incomeTotalPence' => $incomeTotal,
            'income' => $income->map(IncomeSourceData::fromModel(...))->values()->all(),
            'incomeTotal' => Money::format($incomeTotal),
            'categories' => $categories->map(fn (BudgetCategory $category) => BudgetCategoryData::fromModel($category, $budgeted))->values()->all(),
        ];
    }
}
