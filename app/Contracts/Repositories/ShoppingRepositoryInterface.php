<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Enums\Palette;
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

    public function createList(Household $household, string $name, Palette $colour): ShoppingList;

    public function updateList(ShoppingList $list, string $name, Palette $colour): ShoppingList;

    public function deleteList(ShoppingList $list): void;

    public function addItem(ShoppingList $list, string $name, ?string $quantity, ShoppingCategory $category): ShoppingItem;

    public function updateItem(ShoppingItem $item, string $name, ?string $quantity, ShoppingCategory $category): ShoppingItem;

    public function deleteItem(ShoppingItem $item): void;

    public function setItemCompletion(ShoppingItem $item, bool $done): ShoppingItem;
}
