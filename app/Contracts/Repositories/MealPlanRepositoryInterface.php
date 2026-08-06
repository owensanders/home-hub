<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Enums\MealSlot;
use App\Models\Household;
use App\Models\PlannedMeal;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

interface MealPlanRepositoryInterface
{
    /** @return Collection<int, PlannedMeal> */
    public function between(Household $household, CarbonImmutable $from, CarbonImmutable $to): Collection;

    public function forDay(Household $household, CarbonImmutable $day, MealSlot $slot): ?PlannedMeal;

    public function reschedule(PlannedMeal $meal, CarbonImmutable $day): PlannedMeal;

    /** @return Collection<int, \App\Models\Recipe> */
    public function favouriteRecipes(Household $household, int $limit): Collection;

    public function countRecipes(Household $household): int;
}
