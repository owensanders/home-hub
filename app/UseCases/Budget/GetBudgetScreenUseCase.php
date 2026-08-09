<?php

declare(strict_types=1);

namespace App\UseCases\Budget;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Data\BudgetCategoryData;
use App\Data\BudgetTransactionData;
use App\Data\IncomeSourceData;
use App\Models\BudgetCategory;
use App\Models\BudgetTransaction;
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
     *     daysLeftLabel: string, leftToSpend: string, spentLabel: string, budgetedLabel: string, perDay: string,
     *     spentPence: int, budgetedPence: int, incomeTotalPence: int,
     *     income: list<IncomeSourceData>, incomeTotal: string,
     *     categories: list<BudgetCategoryData>, transactions: list<BudgetTransactionData>,
     *     catOptions: list<array{id: int, label: string}>,
     * }
     */
    public function execute(Household $household, CarbonImmutable $month): array
    {
        $today = CarbonImmutable::today();
        $first = $month->startOfMonth();

        $categories = $this->budget->categoriesFor($household, $first);
        $income = $this->budget->incomeSourcesFor($household);
        $transactions = $this->budget->transactionsFor($household, $first);

        $budgeted = (int) $categories->sum('budgeted_pence');
        $spent = (int) $categories->sum('spent_pence');
        $incomeTotal = (int) $income->sum('amount_pence');
        $leftToSpend = $incomeTotal - $spent;

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
            'spentLabel' => Money::format($spent),
            'budgetedLabel' => Money::format($budgeted),
            'perDay' => Money::format($daysLeft > 0 ? intdiv(max($leftToSpend, 0), $daysLeft) : 0),
            'spentPence' => $spent,
            'budgetedPence' => $budgeted,
            'incomeTotalPence' => $incomeTotal,
            'income' => $income->map(IncomeSourceData::fromModel(...))->values()->all(),
            'incomeTotal' => Money::format($incomeTotal),
            'categories' => $categories->map(fn (BudgetCategory $category) => BudgetCategoryData::fromModel($category, $budgeted))->values()->all(),
            'transactions' => $transactions->map(BudgetTransactionData::fromModel(...))->values()->all(),
            'catOptions' => $categories->map(fn (BudgetCategory $category) => ['id' => $category->id, 'label' => $category->label])->values()->all(),
        ];
    }
}
