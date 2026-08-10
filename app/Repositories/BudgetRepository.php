<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Models\BudgetCategory;
use App\Models\Household;
use App\Models\IncomeSource;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BudgetRepository implements BudgetRepositoryInterface
{
    /** @return Collection<int, BudgetCategory> */
    public function categoriesFor(Household $household, CarbonImmutable $month): Collection
    {
        return $household->budgetCategories()
            ->whereDate('month', $month->startOfMonth()->toDateString())
            ->get();
    }

    /** @param array<string, mixed> $attributes */
    public function createCategory(Household $household, CarbonImmutable $month, array $attributes): BudgetCategory
    {
        $position = (int) $household->budgetCategories()
            ->whereDate('month', $month->startOfMonth()->toDateString())
            ->max('position') + 1;

        $category = $household->budgetCategories()->create([
            ...$attributes,
            'month' => $month->startOfMonth(),
            'position' => $position,
        ]);

        // create() doesn't hydrate DB-level defaults (e.g. `colour`) onto the
        // in-memory instance, so refresh before it's used to build a DTO.
        return $category->refresh();
    }

    /** @param array<string, mixed> $attributes */
    public function updateCategory(BudgetCategory $category, array $attributes): BudgetCategory
    {
        $category->update($attributes);

        return $category->refresh();
    }

    public function deleteCategory(BudgetCategory $category): void
    {
        $category->delete();
    }

    /** @return Collection<int, IncomeSource> */
    public function incomeSourcesFor(Household $household): Collection
    {
        return $household->incomeSources()->get();
    }

    /** @param array<string, mixed> $attributes */
    public function createIncomeSource(Household $household, array $attributes): IncomeSource
    {
        $position = (int) $household->incomeSources()->max('position') + 1;

        $income = $household->incomeSources()->create([...$attributes, 'position' => $position]);

        // create() doesn't hydrate DB-level defaults (e.g. `colour`) onto the
        // in-memory instance, so refresh before it's used to build a DTO.
        return $income->refresh();
    }

    /** @param array<string, mixed> $attributes */
    public function updateIncomeSource(IncomeSource $income, array $attributes): IncomeSource
    {
        $income->update($attributes);

        return $income->refresh();
    }

    public function deleteIncomeSource(IncomeSource $income): void
    {
        $income->delete();
    }
}
