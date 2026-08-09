<?php

declare(strict_types=1);

namespace App\UseCases\Shopping;

use App\Contracts\Repositories\ShoppingRepositoryInterface;
use App\Models\ShoppingItem;

class DeleteShoppingItemUseCase
{
    public function __construct(private readonly ShoppingRepositoryInterface $shopping) {}

    public function execute(ShoppingItem $item): void
    {
        $this->shopping->deleteItem($item);
    }
}
