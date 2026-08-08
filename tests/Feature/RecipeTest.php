<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PlannedMeal;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itRendersTheWholeLibraryNotJustFavourites(): void
    {
        $user = User::factory()->create();
        Recipe::factory()->create(['household_id' => $user->household_id, 'is_favourite' => true]);
        Recipe::factory()->count(2)->create(['household_id' => $user->household_id, 'is_favourite' => false]);

        $this->actingAs($user)
            ->get('/recipes')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Recipes')->count('recipes', 3));
    }

    #[Test]
    public function itCreatesARecipe(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/recipes', [
                'name' => 'Veg lasagne',
                'duration_label' => '45 min',
                'difficulty' => 'Medium',
                'tags' => ['veggie'],
                'tint' => 2,
                'is_favourite' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('toast', 'Veg lasagne added');

        $this->assertDatabaseHas('recipes', [
            'household_id' => $user->household_id,
            'name' => 'Veg lasagne',
            'is_favourite' => true,
        ]);
    }

    #[Test]
    public function itUpdatesARecipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['household_id' => $user->household_id, 'name' => 'BBQ burgers']);

        $this->actingAs($user)
            ->patch("/recipes/{$recipe->id}", [
                'name' => 'BBQ beef burgers',
                'is_favourite' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('toast', 'BBQ beef burgers updated');

        $this->assertSame('BBQ beef burgers', $recipe->refresh()->name);
        $this->assertTrue($recipe->is_favourite);
    }

    #[Test]
    public function itDoesNotUpdateAnotherHouseholdsRecipe(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $recipe = Recipe::factory()->create(['household_id' => $stranger->household_id, 'name' => 'BBQ burgers']);

        $this->actingAs($user)
            ->patch("/recipes/{$recipe->id}", ['name' => 'Hijacked'])
            ->assertNotFound();

        $this->assertSame('BBQ burgers', $recipe->refresh()->name);
    }

    #[Test]
    public function itDeletesARecipeAndCascadesItsPlannedMeals(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['household_id' => $user->household_id, 'name' => 'BBQ burgers']);
        PlannedMeal::factory()->create(['household_id' => $user->household_id, 'recipe_id' => $recipe->id]);

        $this->actingAs($user)
            ->delete("/recipes/{$recipe->id}")
            ->assertRedirect()
            ->assertSessionHas('toast', 'BBQ burgers deleted');

        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
        $this->assertDatabaseMissing('planned_meals', ['recipe_id' => $recipe->id]);
    }

    #[Test]
    public function itDoesNotDeleteAnotherHouseholdsRecipe(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $recipe = Recipe::factory()->create(['household_id' => $stranger->household_id]);

        $this->actingAs($user)
            ->delete("/recipes/{$recipe->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('recipes', ['id' => $recipe->id]);
    }
}
