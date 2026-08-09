<?php

declare(strict_types=1);

namespace App\UseCases\Budget;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Data\IncomeSourceData;
use App\Models\IncomeSource;

class UpdateIncomeSourceUseCase
{
    public function __construct(private readonly BudgetRepositoryInterface $budget) {}

    /** @param array<string, mixed> $attributes */
    public function execute(IncomeSource $income, array $attributes): IncomeSourceData
    {
        return IncomeSourceData::fromModel($this->budget->updateIncomeSource($income, $attributes));
    }
}
