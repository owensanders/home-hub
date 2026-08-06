<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ShoppingCategory;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ShoppingItem> */
class ShoppingItemFactory extends Factory
{
    protected $model = ShoppingItem::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'shopping_list_id' => ShoppingList::factory(),
            'name' => $this->faker->word(),
            'quantity' => 'x1',
            'category' => $this->faker->randomElement(ShoppingCategory::cases()),
            'completed_at' => null,
            'position' => 0,
        ];
    }

    public function done(): static
    {
        return $this->state(fn () => ['completed_at' => now()]);
    }
}
