<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Enums\ShoppingCategory;
use App\Models\Household;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use Illuminate\Support\Collection;

interface ShoppingRepositoryInterface
{
    /** @return Collection<int, ShoppingList> lists with their items eager loaded */
    public function listsFor(Household $household): Collection;

    public function findListBySlug(Household $household, string $slug): ?ShoppingList;

    public function defaultListFor(Household $household): ?ShoppingList;

    /** Items still to buy on the household's default list. */
    public function countRemainingOnDefaultList(Household $household): int;

    public function addItem(ShoppingList $list, string $name, ?string $quantity, ShoppingCategory $category): ShoppingItem;

    public function setItemCompletion(ShoppingItem $item, bool $done): ShoppingItem;
}
