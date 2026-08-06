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
}
