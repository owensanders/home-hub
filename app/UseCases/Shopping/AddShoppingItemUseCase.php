<?php

declare(strict_types=1);

namespace App\UseCases\Shopping;

use App\Contracts\Repositories\ShoppingRepositoryInterface;
use App\Data\ShoppingItemData;
use App\Enums\ShoppingCategory;
use App\Models\ShoppingList;

class AddShoppingItemUseCase
{
    public function __construct(private readonly ShoppingRepositoryInterface $shopping) {}

    public function execute(
        ShoppingList $list,
        string $name,
        ?string $quantity = null,
        ?ShoppingCategory $category = null,
    ): ShoppingItemData {
        return ShoppingItemData::fromModel(
            $this->shopping->addItem(
                $list,
                trim($name),
                $quantity ?? 'x1',
                $category ?? ShoppingCategory::Fresh,
            )
        );
    }
}
