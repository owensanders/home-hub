<?php

declare(strict_types=1);

namespace App\UseCases\Budget;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Models\Household;
use Carbon\CarbonImmutable;

class CarryForwardRecurringIncomeUseCase
{
    public function __construct(private readonly BudgetRepositoryInterface $budget) {}

    public function execute(Household $household, CarbonImmutable $month): void
    {
        if ($this->budget->incomeSourcesFor($household, $month)->isNotEmpty()) {
            return;
        }

        foreach ($this->budget->recurringIncomeSourcesBefore($household, $month) as $income) {
            $this->budget->createIncomeSource($household, $month, [
                'label' => $income->label,
                'meta' => $income->meta,
                'colour' => $income->colour,
                'amount_pence' => $income->amount_pence,
                'is_recurring' => true,
            ]);
        }
    }
}
