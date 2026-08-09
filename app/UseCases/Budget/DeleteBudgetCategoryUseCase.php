<?php

declare(strict_types=1);

namespace App\UseCases\Budget;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Models\BudgetCategory;

class DeleteBudgetCategoryUseCase
{
    public function __construct(private readonly BudgetRepositoryInterface $budget) {}

    public function execute(BudgetCategory $category): void
    {
        $this->budget->deleteCategory($category);
    }
}
