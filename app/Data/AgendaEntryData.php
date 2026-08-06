<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\CalendarEvent;
use Spatie\LaravelData\Data;

class AgendaEntryData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public string $time,
        public ?string $who,
        public string $colour,
    ) {}

    public static function fromModel(CalendarEvent $event): self
    {
        return new self(
            id: $event->id,
            title: $event->title,
            time: $event->is_all_day ? 'All day' : $event->starts_at->format('H:i'),
            who: $event->who_label,
            colour: $event->colour->cssVar(),
        );
    }
}
