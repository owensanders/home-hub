<?php

declare(strict_types=1);

namespace App\UseCases\Meals;

use App\Contracts\Repositories\MealPlanRepositoryInterface;
use App\Contracts\Repositories\ShoppingRepositoryInterface;
use App\Data\PlannedMealData;
use App\Models\Household;
use App\UseCases\Meals\Concerns\PushesNewRecipeIngredients;
use App\UseCases\Shopping\AddRecipeIngredientsToListUseCase;

class AddPlannedMealUseCase
{
    use PushesNewRecipeIngredients;

    public function __construct(
        private readonly MealPlanRepositoryInterface $meals,
        private readonly ShoppingRepositoryInterface $shopping,
        private readonly AddRecipeIngredientsToListUseCase $addIngredients,
    ) {}

    /** @param  array<string, mixed>  $attributes */
    public function execute(Household $household, array $attributes): PlannedMealData
    {
        $meal = $this->meals->create($household, $attributes);

        $this->pushNewRecipeIngredients($household, $meal, $attributes);

        return PlannedMealData::fromModel($meal);
    }
}
