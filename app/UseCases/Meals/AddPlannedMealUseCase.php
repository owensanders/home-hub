<?php

declare(strict_types=1);

namespace App\UseCases\Meals;

use App\Contracts\Repositories\MealPlanRepositoryInterface;
use App\Data\PlannedMealData;
use App\Models\Household;

class AddPlannedMealUseCase
{
    public function __construct(private readonly MealPlanRepositoryInterface $meals) {}

    /** @param  array<string, mixed>  $attributes */
    public function execute(Household $household, array $attributes): PlannedMealData
    {
        return PlannedMealData::fromModel($this->meals->create($household, $attributes));
    }
}
