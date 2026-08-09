<?php

declare(strict_types=1);

namespace App\UseCases\Shopping;

use App\Contracts\Repositories\ShoppingRepositoryInterface;
use App\Data\ShoppingListData;
use App\Enums\Palette;
use App\Models\ShoppingList;

class RenameShoppingListUseCase
{
    public function __construct(private readonly ShoppingRepositoryInterface $shopping) {}

    public function execute(ShoppingList $list, string $name, Palette $colour): ShoppingListData
    {
        return ShoppingListData::fromModel($this->shopping->updateList($list, $name, $colour), withItems: false);
    }
}
