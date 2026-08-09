<?php

declare(strict_types=1);

namespace App\UseCases\Budget;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Models\IncomeSource;

class DeleteIncomeSourceUseCase
{
    public function __construct(private readonly BudgetRepositoryInterface $budget) {}

    public function execute(IncomeSource $income): void
    {
        $this->budget->deleteIncomeSource($income);
    }
}
