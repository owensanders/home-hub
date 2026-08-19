<?php

declare(strict_types=1);

namespace App\UseCases\Shopping;

use App\Contracts\Repositories\ShoppingRepositoryInterface;
use App\Data\ShoppingListData;
use App\Models\Recipe;
use App\Models\ShoppingList;

class AddRecipeIngredientsToListUseCase
{
    public function __construct(private readonly ShoppingRepositoryInterface $shopping) {}

    public function execute(Recipe $recipe, ShoppingList $list): ShoppingListData
    {
        foreach ($recipe->ingredients ?? [] as $ingredient) {
            $this->shopping->addItem($list, $ingredient['name'], $ingredient['quantity'] ?? null);
        }

        return ShoppingListData::fromModel($list->refresh());
    }
}
