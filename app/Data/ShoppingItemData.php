<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\ShoppingItem;
use Spatie\LaravelData\Data;

class ShoppingItemData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $quantity,
        public string $category,
        public bool $done,
    ) {}

    public static function fromModel(ShoppingItem $item): self
    {
        return new self(
            id: $item->id,
            name: $item->name,
            quantity: $item->quantity,
            category: $item->category->value,
            done: $item->isDone(),
        );
    }
}
