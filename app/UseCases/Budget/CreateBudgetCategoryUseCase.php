<?php

declare(strict_types=1);

namespace App\UseCases\Budget;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Data\BudgetCategoryData;
use App\Models\Household;
use Carbon\CarbonImmutable;

class CreateBudgetCategoryUseCase
{
    public function __construct(private readonly BudgetRepositoryInterface $budget) {}

    /** @param array<string, mixed> $attributes */
    public function execute(Household $household, CarbonImmutable $month, array $attributes): BudgetCategoryData
    {
        $category = $this->budget->createCategory($household, $month, $attributes);

        return BudgetCategoryData::fromModel($category, $category->budgeted_pence);
    }
}
