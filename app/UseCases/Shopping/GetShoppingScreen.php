<?php

declare(strict_types=1);

namespace App\UseCases\Shopping;

use App\Contracts\Repositories\ShoppingRepositoryInterface;
use App\Data\ShoppingGroupData;
use App\Data\ShoppingItemData;
use App\Data\ShoppingListData;
use App\Enums\ShoppingCategory;
use App\Models\Household;
use App\Models\ShoppingList;

class GetShoppingScreen
{
    public function __construct(private readonly ShoppingRepositoryInterface $shopping) {}

    /**
     * @return array{lists: list<ShoppingListData>, active: ?ShoppingListData, groups: list<ShoppingGroupData>}
     */
    public function execute(Household $household, ?ShoppingList $active): array
    {
        $lists = $this->shopping->listsFor($household);
        $active ??= $lists->first();

        return [
            'lists' => $lists->map(fn (ShoppingList $list) => ShoppingListData::fromModel($list, withItems: false))->all(),
            'active' => $active !== null ? ShoppingListData::fromModel($active) : null,
            'groups' => $active !== null ? $this->groupByAisle($active) : [],
        ];
    }

    /**
     * Groups the list into aisles, keeping the enum's declaration order and
     * dropping any aisle that has nothing in it.
     *
     * @return list<ShoppingGroupData>
     */
    private function groupByAisle(ShoppingList $list): array
    {
        $groups = [];

        foreach (ShoppingCategory::cases() as $category) {
            $items = $list->items
                ->where('category', $category)
                ->map(ShoppingItemData::fromModel(...))
                ->values()
                ->all();

            if ($items !== []) {
                $groups[] = new ShoppingGroupData($category->label(), $items);
            }
        }

        return $groups;
    }
}
