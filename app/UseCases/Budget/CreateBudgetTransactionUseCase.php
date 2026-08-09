<?php

declare(strict_types=1);

namespace App\UseCases\Budget;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Data\BudgetTransactionData;
use App\Models\BudgetCategory;

class CreateBudgetTransactionUseCase
{
    public function __construct(private readonly BudgetRepositoryInterface $budget) {}

    /** @param array<string, mixed> $attributes */
    public function execute(BudgetCategory $category, array $attributes): BudgetTransactionData
    {
        return BudgetTransactionData::fromModel($this->budget->createTransaction($category, $attributes));
    }
}
