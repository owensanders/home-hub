<?php

declare(strict_types=1);

namespace App\UseCases\Meals;

use App\Contracts\Repositories\MealPlanRepositoryInterface;
use App\Models\PlannedMeal;

class DeletePlannedMealUseCase
{
    public function __construct(private readonly MealPlanRepositoryInterface $meals) {}

    public function execute(PlannedMeal $meal): void
    {
        $this->meals->delete($meal);
    }
}
