<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\CalendarEvent;
use Spatie\LaravelData\Data;

class CalendarEventData extends Data
{
    /** @param list<MemberData> $attendees */
    public function __construct(
        public int $id,
        public string $title,
        public string $date,
        public string $time,
        public string $who,
        public bool $isAllDay,
        public ?string $location,
        public ?string $notes,
        public string $colour,
        public string $colourKey,
        public array $attendees,
        /** Raw values for the edit form, which needs to round-trip them back. */
        public string $startsAt,
        public ?string $endsAt,
    ) {}

    public static function fromModel(CalendarEvent $event): self
    {
        return new self(
            id: $event->id,
            title: $event->title,
            date: $event->starts_at->toDateString(),
            time: $event->timeLabel(),
            who: $event->whoLabel(),
            isAllDay: $event->is_all_day,
            location: $event->location,
            notes: $event->notes,
            colour: $event->colour->cssVar(),
            colourKey: $event->colour->value,
            attendees: $event->attendees->map(MemberData::fromModel(...))->values()->all(),
            startsAt: $event->starts_at->format('Y-m-d\TH:i'),
            endsAt: $event->ends_at?->format('Y-m-d\TH:i'),
        );
    }
}
