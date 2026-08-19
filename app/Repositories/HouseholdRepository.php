<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\HouseholdRepositoryInterface;
use App\Models\BudgetCategory;
use App\Models\CalendarEvent;
use App\Models\Household;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class HouseholdRepository implements HouseholdRepositoryInterface
{
    /** @param  array<string, mixed>  $attributes */
    public function create(array $attributes): Household
    {
        return Household::create($attributes);
    }

    /** @param  array<string, mixed>  $attributes */
    public function update(Household $household, array $attributes): Household
    {
        $household->update($attributes);

        return $household->refresh();
    }

    public function findByJoinCode(string $joinCode): ?Household
    {
        return Household::where('join_code', $joinCode)->first();
    }

    /** @return Collection<int, User> */
    public function members(Household $household): Collection
    {
        return $household->members()->orderBy('id')->get();
    }

    /** @return Collection<int, CalendarEvent> */
    public function eventsBetween(Household $household, CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return $household->calendarEvents()
            ->with('attendees')
            ->whereBetween('starts_at', [$from, $to])
            ->orderBy('starts_at')
            ->get();
    }

    /** @return Collection<int, BudgetCategory> */
    public function budgetFor(Household $household, CarbonImmutable $month): Collection
    {
        return $household->budgetCategories()
            ->whereDate('month', $month->startOfMonth()->toDateString())
            ->get();
    }

    public function delete(Household $household): void
    {
        $household->delete();
    }
}
