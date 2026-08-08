<?php

declare(strict_types=1);

namespace App\UseCases\Meals;

use App\Contracts\Repositories\MealPlanRepositoryInterface;
use App\Models\Recipe;

class DeleteRecipeUseCase
{
    public function __construct(private readonly MealPlanRepositoryInterface $meals) {}

    public function execute(Recipe $recipe): void
    {
        $this->meals->deleteRecipe($recipe);
    }
}
