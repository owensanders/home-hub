<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ShoppingCategory;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShoppingListTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itGroupsItemsByAisleInEnumOrderAndSkipsEmptyAisles(): void
    {
        $user = User::factory()->create();
        $list = ShoppingList::factory()->create(['household_id' => $user->household_id, 'slug' => 'tesco']);

        ShoppingItem::factory()->create(['shopping_list_id' => $list->id, 'category' => ShoppingCategory::Household]);
        ShoppingItem::factory()->create(['shopping_list_id' => $list->id, 'category' => ShoppingCategory::Fruit]);

        $this->actingAs($user)
            ->get('/shopping/tesco')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('ShoppingLists')
                ->where('groups.0.label', 'Fruit')
                ->where('groups.1.label', 'Household')
                ->count('groups', 2)
            );
    }

    #[Test]
    public function itFallsBackToTheFirstListWhenNoSlugIsGiven(): void
    {
        $user = User::factory()->create();
        ShoppingList::factory()->create(['household_id' => $user->household_id, 'name' => 'Tesco', 'position' => 0]);
        ShoppingList::factory()->create(['household_id' => $user->household_id, 'name' => 'Aldi', 'position' => 1]);

        $this->actingAs($user)
            ->get('/shopping')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('active.name', 'Tesco')->count('lists', 2));
    }

    #[Test]
    public function itAddsAnItemToTheEndOfTheList(): void
    {
        $user = User::factory()->create();
        $list = ShoppingList::factory()->create(['household_id' => $user->household_id, 'slug' => 'tesco']);
        ShoppingItem::factory()->create(['shopping_list_id' => $list->id, 'position' => 4]);

        $this->actingAs($user)
            ->post('/shopping/tesco/items', ['name' => 'Olive oil'])
            ->assertRedirect()
            ->assertSessionHas('toast');

        $added = ShoppingItem::where('name', 'Olive oil')->sole();

        $this->assertSame(5, $added->position);
        $this->assertSame('x1', $added->quantity);
        $this->assertFalse($added->isDone());
    }

    #[Test]
    public function itRejectsAnItemWithoutAName(): void
    {
        $user = User::factory()->create();
        ShoppingList::factory()->create(['household_id' => $user->household_id, 'slug' => 'tesco']);

        $this->actingAs($user)
            ->post('/shopping/tesco/items', ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    #[Test]
    public function itTogglesAnItemBothWays(): void
    {
        $user = User::factory()->create();
        $list = ShoppingList::factory()->create(['household_id' => $user->household_id]);
        $item = ShoppingItem::factory()->create(['shopping_list_id' => $list->id]);

        $this->actingAs($user)->patch("/shopping-items/{$item->id}/toggle")->assertRedirect();
        $this->assertTrue($item->refresh()->isDone());

        $this->actingAs($user)->patch("/shopping-items/{$item->id}/toggle")->assertRedirect();
        $this->assertFalse($item->refresh()->isDone());
    }

    #[Test]
    public function itDoesNotLeakItemsFromAnotherHousehold(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        $theirList = ShoppingList::factory()->create(['household_id' => $stranger->household_id, 'slug' => 'theirs']);
        $theirItem = ShoppingItem::factory()->create(['shopping_list_id' => $theirList->id]);

        $this->actingAs($user)->get('/shopping/theirs')->assertNotFound();
        $this->actingAs($user)->post('/shopping/theirs/items', ['name' => 'Milk'])->assertNotFound();
        $this->actingAs($user)->patch("/shopping-items/{$theirItem->id}/toggle")->assertNotFound();

        $this->assertFalse($theirItem->refresh()->isDone());
    }
}
