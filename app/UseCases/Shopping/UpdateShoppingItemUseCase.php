<?php

declare(strict_types=1);

namespace App\UseCases\Shopping;

use App\Contracts\Repositories\ShoppingRepositoryInterface;
use App\Data\ShoppingItemData;
use App\Enums\ShoppingCategory;
use App\Models\ShoppingItem;

class UpdateShoppingItemUseCase
{
    public function __construct(private readonly ShoppingRepositoryInterface $shopping) {}

    public function execute(ShoppingItem $item, string $name, ?string $quantity, ?string $category): ShoppingItemData
    {
        return ShoppingItemData::fromModel($this->shopping->updateItem(
            $item,
            trim($name),
            $quantity,
            ShoppingCategory::tryFrom((string) $category) ?? $item->category,
        ));
    }
}
