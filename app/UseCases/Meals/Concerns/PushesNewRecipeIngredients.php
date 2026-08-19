<?php

declare(strict_types=1);

namespace App\UseCases\Meals\Concerns;

use App\Models\Household;
use App\Models\PlannedMeal;

trait PushesNewRecipeIngredients
{
    /** @param  array<string, mixed>  $attributes */
    protected function pushNewRecipeIngredients(Household $household, PlannedMeal $meal, array $attributes): void
    {
        $listId = $attributes['new_recipe_shopping_list_id'] ?? null;

        if ($listId === null) {
            return;
        }

        $list = $this->shopping->listsFor($household)->firstWhere('id', $listId);

        if ($list === null) {
            return;
        }

        $this->addIngredients->execute($meal->recipe, $list);
    }
}
