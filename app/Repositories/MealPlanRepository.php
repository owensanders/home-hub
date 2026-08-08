<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\MealPlanRepositoryInterface;
use App\Enums\MealSlot;
use App\Models\Household;
use App\Models\PlannedMeal;
use App\Models\Recipe;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MealPlanRepository implements MealPlanRepositoryInterface
{
    /** @return Collection<int, PlannedMeal> */
    public function between(Household $household, CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return $household->plannedMeals()
            ->with(['recipe', 'cook'])
            ->whereBetween('planned_on', [$from->toDateString(), $to->toDateString()])
            ->orderBy('planned_on')
            ->orderBy('id')
            ->get();
    }

    public function forDay(Household $household, CarbonImmutable $day, MealSlot $slot): ?PlannedMeal
    {
        return $household->plannedMeals()
            ->with(['recipe', 'cook'])
            ->whereDate('planned_on', $day->toDateString())
            ->where('slot', $slot)
            ->first();
    }

    public function reschedule(PlannedMeal $meal, CarbonImmutable $day): PlannedMeal
    {
        $meal->update(['planned_on' => $day->toDateString()]);

        return $meal->refresh();
    }

    /** @param  array<string, mixed>  $attributes */
    public function create(Household $household, array $attributes): PlannedMeal
    {
        return DB::transaction(function () use ($household, $attributes) {
            $attributes['recipe_id'] = $this->resolveRecipeId($household, $attributes);
            unset($attributes['new_recipe_name'], $attributes['new_recipe_description']);

            $meal = $household->plannedMeals()->create($attributes);

            return $meal->refresh()->load(['recipe', 'cook']);
        });
    }

    /** @param  array<string, mixed>  $attributes */
    public function update(PlannedMeal $meal, array $attributes): PlannedMeal
    {
        return DB::transaction(function () use ($meal, $attributes) {
            $attributes['recipe_id'] = $this->resolveRecipeId($meal->household, $attributes);
            unset($attributes['new_recipe_name'], $attributes['new_recipe_description']);

            $meal->update($attributes);

            return $meal->refresh()->load(['recipe', 'cook']);
        });
    }

    public function delete(PlannedMeal $meal): void
    {
        $meal->delete();
    }

    /** @return Collection<int, Recipe> */
    public function favouriteRecipes(Household $household, int $limit): Collection
    {
        return $household->recipes()
            ->where('is_favourite', true)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function countRecipes(Household $household): int
    {
        return $household->recipes()->count();
    }

    /** @return Collection<int, Recipe> */
    public function allRecipes(Household $household): Collection
    {
        return $household->recipes()
            ->orderByDesc('is_favourite')
            ->orderBy('name')
            ->get();
    }

    /** @param  array<string, mixed>  $attributes */
    public function createRecipe(Household $household, array $attributes): Recipe
    {
        return $household->recipes()->create($attributes);
    }

    /** @param  array<string, mixed>  $attributes */
    public function updateRecipe(Recipe $recipe, array $attributes): Recipe
    {
        $recipe->update($attributes);

        return $recipe->refresh();
    }

    public function deleteRecipe(Recipe $recipe): void
    {
        $recipe->delete();
    }

    /** @param  array<string, mixed>  $attributes */
    private function resolveRecipeId(Household $household, array $attributes): int
    {
        if (! empty($attributes['recipe_id'])) {
            return (int) $attributes['recipe_id'];
        }

        return $household->recipes()->create([
            'name' => trim((string) $attributes['new_recipe_name']),
            'description' => $attributes['new_recipe_description'] ?? null,
        ])->id;
    }
}
