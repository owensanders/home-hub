<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\ShoppingRepositoryInterface;
use App\Enums\ShoppingCategory;
use App\Models\Household;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use Illuminate\Support\Collection;


class ShoppingRepository implements ShoppingRepositoryInterface
{
    /** @return Collection<int, ShoppingList> */
    public function listsFor(Household $household): Collection
    {
        return $household->shoppingLists()->with('items')->get();
    }

    public function findListBySlug(Household $household, string $slug): ?ShoppingList
    {
        return $household->shoppingLists()->with('items')->where('slug', $slug)->first();
    }

    public function defaultListFor(Household $household): ?ShoppingList
    {
        return $household->shoppingLists()->with('items')->first();
    }

    public function countRemainingOnDefaultList(Household $household): int
    {
        $listId = $household->shoppingLists()->value('id');

        if ($listId === null) {
            return 0;
        }

        return ShoppingItem::where('shopping_list_id', $listId)->whereNull('completed_at')->count();
    }

    public function addItem(ShoppingList $list, string $name, ?string $quantity, ShoppingCategory $category): ShoppingItem
    {
        return $list->items()->create([
            'name' => $name,
            'quantity' => $quantity,
            'category' => $category,
            'position' => (int) $list->items()->max('position') + 1,
        ]);
    }

    public function setItemCompletion(ShoppingItem $item, bool $done): ShoppingItem
    {
        $item->update(['completed_at' => $done ? now() : null]);

        return $item->refresh();
    }
}
