<?php

declare(strict_types=1);

namespace App\UseCases\Meals;

use App\Contracts\Repositories\MealPlanRepositoryInterface;
use App\Data\RecipeData;
use App\Models\Household;

class AddRecipeUseCase
{
    public function __construct(private readonly MealPlanRepositoryInterface $meals) {}

    /** @param  array<string, mixed>  $attributes */
    public function execute(Household $household, array $attributes): RecipeData
    {
        return RecipeData::fromModel($this->meals->createRecipe($household, $attributes));
    }
}
