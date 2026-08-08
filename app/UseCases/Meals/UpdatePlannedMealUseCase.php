<?php

declare(strict_types=1);

namespace App\UseCases\Meals;

use App\Contracts\Repositories\MealPlanRepositoryInterface;
use App\Data\PlannedMealData;
use App\Models\PlannedMeal;

class UpdatePlannedMealUseCase
{
    public function __construct(private readonly MealPlanRepositoryInterface $meals) {}

    /** @param  array<string, mixed>  $attributes */
    public function execute(PlannedMeal $meal, array $attributes): PlannedMealData
    {
        return PlannedMealData::fromModel($this->meals->update($meal, $attributes));
    }
}
