<?php

declare(strict_types=1);

namespace App\UseCases\Budget;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Data\IncomeSourceData;
use App\Models\Household;
use Carbon\CarbonImmutable;

class CreateIncomeSourceUseCase
{
    public function __construct(private readonly BudgetRepositoryInterface $budget) {}

    /** @param array<string, mixed> $attributes */
    public function execute(Household $household, CarbonImmutable $month, array $attributes): IncomeSourceData
    {
        return IncomeSourceData::fromModel($this->budget->createIncomeSource($household, $month, $attributes));
    }
}
