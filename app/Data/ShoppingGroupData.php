<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

/**
 * A single aisle heading plus the items filed under it.
 */
class ShoppingGroupData extends Data
{
    /** @param list<ShoppingItemData> $items */
    public function __construct(
        public string $label,
        public array $items,
    ) {}
}
