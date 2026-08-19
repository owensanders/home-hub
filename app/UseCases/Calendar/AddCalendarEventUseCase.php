<?php

declare(strict_types=1);

namespace App\UseCases\Calendar;

use App\Contracts\Repositories\CalendarEventRepositoryInterface;
use App\Data\CalendarEventData;
use App\Models\Household;

class AddCalendarEventUseCase
{
    public function __construct(private readonly CalendarEventRepositoryInterface $events) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<int>  $attendeeIds
     */
    public function execute(Household $household, array $attributes, array $attendeeIds): CalendarEventData
    {
        $event = $this->events->create($household, $attributes, $attendeeIds);

        return CalendarEventData::fromModel($event, $event->starts_at->toDateString());
    }
}
