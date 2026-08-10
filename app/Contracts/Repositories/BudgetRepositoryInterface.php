<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\BudgetCategory;
use App\Models\Household;
use App\Models\IncomeSource;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

interface BudgetRepositoryInterface
{
    /** @return Collection<int, BudgetCategory> */
    public function categoriesFor(Household $household, CarbonImmutable $month): Collection;

    /** @param array<string, mixed> $attributes */
    public function createCategory(Household $household, CarbonImmutable $month, array $attributes): BudgetCategory;

    /** @param array<string, mixed> $attributes */
    public function updateCategory(BudgetCategory $category, array $attributes): BudgetCategory;

    public function deleteCategory(BudgetCategory $category): void;

    /** @return Collection<int, BudgetCategory> the nearest earlier month's recurring categories, or empty if none */
    public function recurringCategoriesBefore(Household $household, CarbonImmutable $month): Collection;

    /** @return Collection<int, IncomeSource> */
    public function incomeSourcesFor(Household $household): Collection;

    /** @param array<string, mixed> $attributes */
    public function createIncomeSource(Household $household, array $attributes): IncomeSource;

    /** @param array<string, mixed> $attributes */
    public function updateIncomeSource(IncomeSource $income, array $attributes): IncomeSource;

    public function deleteIncomeSource(IncomeSource $income): void;
}
