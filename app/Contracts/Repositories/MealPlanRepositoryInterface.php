<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Enums\MealSlot;
use App\Models\Household;
use App\Models\PlannedMeal;
use App\Models\Recipe;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

interface MealPlanRepositoryInterface
{
    /** @return Collection<int, PlannedMeal> */
    public function between(Household $household, CarbonImmutable $from, CarbonImmutable $to): Collection;

    public function forDay(Household $household, CarbonImmutable $day, MealSlot $slot): ?PlannedMeal;

    public function reschedule(PlannedMeal $meal, CarbonImmutable $day): PlannedMeal;

    /** @param  array<string, mixed>  $attributes */
    public function create(Household $household, array $attributes): PlannedMeal;

    /** @param  array<string, mixed>  $attributes */
    public function update(PlannedMeal $meal, array $attributes): PlannedMeal;

    public function delete(PlannedMeal $meal): void;

    /** @return Collection<int, Recipe> */
    public function favouriteRecipes(Household $household, int $limit): Collection;

    public function countRecipes(Household $household): int;

    /** @return Collection<int, Recipe> */
    public function allRecipes(Household $household): Collection;

    /** @param  array<string, mixed>  $attributes */
    public function createRecipe(Household $household, array $attributes): Recipe;

    /** @param  array<string, mixed>  $attributes */
    public function updateRecipe(Recipe $recipe, array $attributes): Recipe;

    public function deleteRecipe(Recipe $recipe): void;
}
