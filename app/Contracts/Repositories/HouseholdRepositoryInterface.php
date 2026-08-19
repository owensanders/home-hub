<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\BudgetCategory;
use App\Models\CalendarEvent;
use App\Models\Household;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

interface HouseholdRepositoryInterface
{
    /** @param  array<string, mixed>  $attributes */
    public function create(array $attributes): Household;

    /** @param  array<string, mixed>  $attributes */
    public function update(Household $household, array $attributes): Household;

    public function findByJoinCode(string $joinCode): ?Household;

    /** @return Collection<int, \App\Models\User> */
    public function members(Household $household): Collection;

    /** @return Collection<int, CalendarEvent> */
    public function eventsBetween(Household $household, CarbonImmutable $from, CarbonImmutable $to): Collection;

    /** @return Collection<int, BudgetCategory> */
    public function budgetFor(Household $household, CarbonImmutable $month): Collection;

    public function delete(Household $household): void;
}
