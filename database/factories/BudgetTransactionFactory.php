<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BudgetCategory;
use App\Models\BudgetTransaction;
use App\Models\Household;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BudgetTransaction> */
class BudgetTransactionFactory extends Factory
{
    protected $model = BudgetTransaction::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'budget_category_id' => BudgetCategory::factory(),
            'month' => CarbonImmutable::now()->startOfMonth(),
            'label' => $this->faker->words(2, true),
            'amount_pence' => 1000,
        ];
    }
}
