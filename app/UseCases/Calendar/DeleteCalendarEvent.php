<?php

declare(strict_types=1);

namespace App\UseCases\Calendar;

use App\Contracts\Repositories\CalendarEventRepositoryInterface;
use App\Models\CalendarEvent;

class DeleteCalendarEvent
{
    public function __construct(private readonly CalendarEventRepositoryInterface $events) {}

    public function execute(CalendarEvent $event): void
    {
        $this->events->delete($event);
    }
}
