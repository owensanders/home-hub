<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class AgendaGroupData extends Data
{
    /** @param list<AgendaEntryData> $items */
    public function __construct(
        public string $label,
        public array $items,
    ) {}
}
