<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Aisle groupings for a shopping list. Declaration order is the order the
 * groups are rendered in, which roughly matches how you walk round a shop.
 */
enum ShoppingCategory: string
{
    case Fruit = 'fruit';
    case Vegetables = 'vegetables';
    case Fresh = 'fresh';
    case Frozen = 'frozen';
    case Bakery = 'bakery';
    case Household = 'household';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
