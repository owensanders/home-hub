<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\BudgetCategory;
use App\Support\Money;
use Spatie\LaravelData\Data;

class BudgetCategoryData extends Data
{
    public function __construct(
        public int $id,
        public string $label,
        public string $colour,
        public string $spent,
        public int $percentageOfBudget,
        public int $shareOfTotal,
    ) {}

    /**
     * @param  int  $totalBudgetPence  used for the stacked bar at the top of the card,
     * where each segment is sized against the whole budget.
     */
    public static function fromModel(BudgetCategory $category, int $totalBudgetPence): self
    {
        return new self(
            id: $category->id,
            label: $category->label,
            colour: $category->colour->cssVar(),
            spent: Money::format($category->spent_pence),
            percentageOfBudget: $category->budgeted_pence > 0
                ? (int) round($category->spent_pence / $category->budgeted_pence * 100)
                : 0,
            shareOfTotal: $totalBudgetPence > 0
                ? (int) round($category->spent_pence / $totalBudgetPence * 100)
                : 0,
        );
    }
}
