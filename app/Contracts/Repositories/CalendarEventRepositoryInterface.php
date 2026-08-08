<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\CalendarEvent;
use App\Models\Household;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

interface CalendarEventRepositoryInterface
{
    /** @return Collection<int, CalendarEvent> */
    public function between(Household $household, CarbonImmutable $from, CarbonImmutable $to): Collection;

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<int>  $attendeeIds
     */
    public function create(Household $household, array $attributes, array $attendeeIds): CalendarEvent;

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<int>  $attendeeIds
     */
    public function update(CalendarEvent $event, array $attributes, array $attendeeIds): CalendarEvent;

    public function delete(CalendarEvent $event): void;
}
