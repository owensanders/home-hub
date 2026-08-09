<?php

declare(strict_types=1);

namespace App\UseCases\Budget;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Data\BudgetCategoryData;
use App\Models\BudgetCategory;

class UpdateBudgetCategoryUseCase
{
    public function __construct(private readonly BudgetRepositoryInterface $budget) {}

    /** @param array<string, mixed> $attributes */
    public function execute(BudgetCategory $category, array $attributes): BudgetCategoryData
    {
        $updated = $this->budget->updateCategory($category, $attributes);

        return BudgetCategoryData::fromModel($updated, $updated->budgeted_pence);
    }
}
