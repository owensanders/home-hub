<?php

declare(strict_types=1);

namespace App\UseCases\Budget;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Data\BudgetTransactionData;
use App\Models\BudgetCategory;
use App\Models\BudgetTransaction;

class MoveBudgetTransactionUseCase
{
    public function __construct(private readonly BudgetRepositoryInterface $budget) {}

    public function execute(BudgetTransaction $transaction, BudgetCategory $newCategory): BudgetTransactionData
    {
        return BudgetTransactionData::fromModel($this->budget->moveTransaction($transaction, $newCategory));
    }
}
