<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class ChoreColumnData extends Data
{
    /** @param list<ChoreData> $items */
    public function __construct(
        public string $status,
        public string $title,
        public int $count,
        public array $items,
    ) {}
}
