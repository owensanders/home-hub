<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PlannedMeal;
use App\Models\Recipe;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MealPlannerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Pin to a Wednesday so week boundaries are unambiguous.
        CarbonImmutable::setTestNow('2026-08-05 09:00:00');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function itRendersSevenDaysStartingOnMondayAndFilesMealsUnderTheirDay(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['household_id' => $user->household_id, 'name' => 'Veg lasagne']);

        PlannedMeal::factory()->create([
            'household_id' => $user->household_id,
            'recipe_id' => $recipe->id,
            'cook_id' => $user->id,
            'planned_on' => '2026-08-07',
        ]);

        $this->actingAs($user)
            ->get('/meals')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('MealPlanner')
                ->count('days', 7)
                ->where('days.0.date', '2026-08-03')
                ->where('days.0.dayLabel', 'Mon')
                ->where('days.2.isToday', true)
                ->count('days.4.meals', 1)
                ->where('days.4.meals.0.name', 'Veg lasagne')
                ->where('days.4.meals.0.missingLabel', 'All in')
            );
    }

    #[Test]
    public function itOnlyListsFavouritesInTheRecipeLibrary(): void
    {
        $user = User::factory()->create();
        Recipe::factory()->create(['household_id' => $user->household_id, 'is_favourite' => true]);
        Recipe::factory()->count(2)->create(['household_id' => $user->household_id, 'is_favourite' => false]);

        $this->actingAs($user)
            ->get('/meals')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->count('library', 1)->where('recipeCount', 3));
    }

    #[Test]
    public function itReschedulesAMealToAnotherDay(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['household_id' => $user->household_id, 'name' => 'BBQ burgers']);
        $meal = PlannedMeal::factory()->create([
            'household_id' => $user->household_id,
            'recipe_id' => $recipe->id,
            'planned_on' => '2026-08-05',
        ]);

        $this->actingAs($user)
            ->patch("/meals/{$meal->id}/reschedule", ['planned_on' => '2026-08-08'])
            ->assertRedirect()
            ->assertSessionHas('toast', 'BBQ burgers moved');

        $this->assertSame('2026-08-08', $meal->refresh()->planned_on->toDateString());
    }

    #[Test]
    public function itDoesNotRescheduleAnotherHouseholdsMeal(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $meal = PlannedMeal::factory()->create([
            'household_id' => $stranger->household_id,
            'planned_on' => '2026-08-05',
        ]);

        $this->actingAs($user)
            ->patch("/meals/{$meal->id}/reschedule", ['planned_on' => '2026-08-08'])
            ->assertNotFound();

        $this->assertSame('2026-08-05', $meal->refresh()->planned_on->toDateString());
    }

    #[Test]
    public function itRejectsAWeekItCannotUnderstand(): void
    {
        $user = User::factory()->create();

        // `?week=garbage` used to reach Carbon and 500.
        foreach (['garbage', '2026-13-01', '08/08/2026'] as $week) {
            $this->actingAs($user)
                ->get("/meals?week={$week}")
                ->assertRedirect('/meals')
                ->assertSessionHasErrors('week');
        }
    }

    #[Test]
    public function itRendersTheWeekAskedFor(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/meals?week=2026-08-05')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('MealPlanner')->where('days.0.date', '2026-08-03'));
    }

    #[Test]
    public function itAddsAMealFromAnExistingRecipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['household_id' => $user->household_id, 'name' => 'Veg lasagne']);

        $this->actingAs($user)
            ->post('/meals', [
                'recipe_id' => $recipe->id,
                'planned_on' => '2026-08-07',
                'slot' => 'dinner',
            ])
            ->assertRedirect()
            ->assertSessionHas('toast', 'Veg lasagne added');

        $this->assertDatabaseHas('planned_meals', [
            'household_id' => $user->household_id,
            'recipe_id' => $recipe->id,
            'planned_on' => '2026-08-07',
        ]);
    }

    #[Test]
    public function itAddsAMealWithANewRecipeCreatedOnTheFly(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/meals', [
                'new_recipe_name' => 'Sunday roast',
                'new_recipe_description' => 'Beef, all the trimmings',
                'planned_on' => '2026-08-07',
                'slot' => 'dinner',
            ])
            ->assertRedirect()
            ->assertSessionHas('toast', 'Sunday roast added');

        $this->assertDatabaseHas('recipes', [
            'household_id' => $user->household_id,
            'name' => 'Sunday roast',
            'description' => 'Beef, all the trimmings',
        ]);
        $this->assertDatabaseHas('planned_meals', ['household_id' => $user->household_id, 'planned_on' => '2026-08-07']);
    }

    #[Test]
    public function itUpdatesAMeal(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['household_id' => $user->household_id]);
        $otherRecipe = Recipe::factory()->create(['household_id' => $user->household_id, 'name' => 'Fish tacos']);
        $meal = PlannedMeal::factory()->create([
            'household_id' => $user->household_id,
            'recipe_id' => $recipe->id,
            'planned_on' => '2026-08-05',
            'slot' => 'dinner',
        ]);

        $this->actingAs($user)
            ->patch("/meals/{$meal->id}", [
                'recipe_id' => $otherRecipe->id,
                'planned_on' => '2026-08-05',
                'slot' => 'lunch',
            ])
            ->assertRedirect()
            ->assertSessionHas('toast', 'Fish tacos updated');

        $meal->refresh();
        $this->assertSame($otherRecipe->id, $meal->recipe_id);
        $this->assertSame('lunch', $meal->slot->value);
    }

    #[Test]
    public function itDoesNotUpdateAnotherHouseholdsMeal(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['household_id' => $user->household_id]);
        $stranger = User::factory()->create();
        $meal = PlannedMeal::factory()->create(['household_id' => $stranger->household_id, 'planned_on' => '2026-08-05']);

        $this->actingAs($user)
            ->patch("/meals/{$meal->id}", [
                'recipe_id' => $recipe->id,
                'planned_on' => '2026-08-06',
                'slot' => 'dinner',
            ])
            ->assertNotFound();
    }

    #[Test]
    public function itDeletesAMeal(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['household_id' => $user->household_id, 'name' => 'BBQ burgers']);
        $meal = PlannedMeal::factory()->create(['household_id' => $user->household_id, 'recipe_id' => $recipe->id]);

        $this->actingAs($user)
            ->delete("/meals/{$meal->id}")
            ->assertRedirect()
            ->assertSessionHas('toast', 'BBQ burgers removed');

        $this->assertDatabaseMissing('planned_meals', ['id' => $meal->id]);
    }

    #[Test]
    public function itDoesNotDeleteAnotherHouseholdsMeal(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $meal = PlannedMeal::factory()->create(['household_id' => $stranger->household_id]);

        $this->actingAs($user)
            ->delete("/meals/{$meal->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('planned_meals', ['id' => $meal->id]);
    }
}
