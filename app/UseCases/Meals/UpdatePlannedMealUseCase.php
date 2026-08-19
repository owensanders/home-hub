<?php

declare(strict_types=1);

namespace App\UseCases\Meals;

use App\Contracts\Repositories\MealPlanRepositoryInterface;
use App\Contracts\Repositories\ShoppingRepositoryInterface;
use App\Data\PlannedMealData;
use App\Models\PlannedMeal;
use App\UseCases\Meals\Concerns\PushesNewRecipeIngredients;
use App\UseCases\Shopping\AddRecipeIngredientsToListUseCase;

class UpdatePlannedMealUseCase
{
    use PushesNewRecipeIngredients;

    public function __construct(
        private readonly MealPlanRepositoryInterface $meals,
        private readonly ShoppingRepositoryInterface $shopping,
        private readonly AddRecipeIngredientsToListUseCase $addIngredients,
    ) {}

    /** @param  array<string, mixed>  $attributes */
    public function execute(PlannedMeal $meal, array $attributes): PlannedMealData
    {
        $updated = $this->meals->update($meal, $attributes);

        $this->pushNewRecipeIngredients($updated->household, $updated, $attributes);

        return PlannedMealData::fromModel($updated);
    }
}
