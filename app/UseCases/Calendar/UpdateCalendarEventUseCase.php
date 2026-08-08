<?php

declare(strict_types=1);

namespace App\UseCases\Calendar;

use App\Contracts\Repositories\CalendarEventRepositoryInterface;
use App\Data\CalendarEventData;
use App\Models\CalendarEvent;

class UpdateCalendarEventUseCase
{
    public function __construct(private readonly CalendarEventRepositoryInterface $events) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<int>  $attendeeIds
     */
    public function execute(CalendarEvent $event, array $attributes, array $attendeeIds): CalendarEventData
    {
        return CalendarEventData::fromModel(
            $this->events->update($event, $attributes, $attendeeIds)
        );
    }
}
