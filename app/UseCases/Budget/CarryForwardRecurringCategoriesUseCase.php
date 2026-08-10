<?php

declare(strict_types=1);

namespace App\UseCases\Budget;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Models\Household;
use Carbon\CarbonImmutable;

class CarryForwardRecurringCategoriesUseCase
{
    public function __construct(private readonly BudgetRepositoryInterface $budget) {}

    public function execute(Household $household, CarbonImmutable $month): void
    {
        if ($this->budget->categoriesFor($household, $month)->isNotEmpty()) {
            return;
        }

        foreach ($this->budget->recurringCategoriesBefore($household, $month) as $category) {
            $this->budget->createCategory($household, $month, [
                'label' => $category->label,
                'icon' => $category->icon,
                'colour' => $category->colour,
                'budgeted_pence' => $category->budgeted_pence,
                'is_recurring' => true,
            ]);
        }
    }
}
