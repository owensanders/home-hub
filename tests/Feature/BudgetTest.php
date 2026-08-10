<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BudgetCategory;
use App\Models\IncomeSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itRendersCategoriesAndIncomeForTheMonth(): void
    {
        $user = User::factory()->create();
        BudgetCategory::factory()->create([
            'household_id' => $user->household_id,
            'label' => 'Food & shopping',
            'budgeted_pence' => 50000,
        ]);
        IncomeSource::factory()->create(['household_id' => $user->household_id, 'amount_pence' => 200000]);

        $this->actingAs($user)
            ->get('/budget')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Budget')
                ->count('categories', 1)
                ->count('income', 1)
                ->where('categories.0.label', 'Food & shopping')
            );
    }

    #[Test]
    public function itCreatesACategoryAppendedToTheViewedMonth(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/budget/categories', ['label' => 'Pets', 'budgeted' => '75'])
            ->assertRedirect()
            ->assertSessionHas('toast', 'Pets added');

        $this->assertDatabaseHas('budget_categories', [
            'household_id' => $user->household_id,
            'label' => 'Pets',
            'budgeted_pence' => 7500,
        ]);
    }

    #[Test]
    public function itUpdatesACategory(): void
    {
        $user = User::factory()->create();
        $category = BudgetCategory::factory()->create(['household_id' => $user->household_id, 'label' => 'Fuel']);

        $this->actingAs($user)
            ->patch("/budget/categories/{$category->id}", ['label' => 'Transport & fuel', 'budgeted' => '220'])
            ->assertRedirect()
            ->assertSessionHas('toast', 'Transport & fuel updated');

        $category->refresh();
        $this->assertSame('Transport & fuel', $category->label);
        $this->assertSame(22000, $category->budgeted_pence);
    }

    #[Test]
    public function itDoesNotUpdateAnotherHouseholdsCategory(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $category = BudgetCategory::factory()->create(['household_id' => $stranger->household_id, 'label' => 'Fuel']);

        $this->actingAs($user)
            ->patch("/budget/categories/{$category->id}", ['label' => 'Hijacked', 'budgeted' => '10'])
            ->assertNotFound();

        $this->assertSame('Fuel', $category->refresh()->label);
    }

    #[Test]
    public function itDeletesACategory(): void
    {
        $user = User::factory()->create();
        $category = BudgetCategory::factory()->create(['household_id' => $user->household_id, 'label' => 'Subscriptions']);

        $this->actingAs($user)
            ->delete("/budget/categories/{$category->id}")
            ->assertRedirect()
            ->assertSessionHas('toast', 'Subscriptions removed');

        $this->assertDatabaseMissing('budget_categories', ['id' => $category->id]);
    }

    #[Test]
    public function itDoesNotDeleteAnotherHouseholdsCategory(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $category = BudgetCategory::factory()->create(['household_id' => $stranger->household_id]);

        $this->actingAs($user)
            ->delete("/budget/categories/{$category->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('budget_categories', ['id' => $category->id]);
    }

    #[Test]
    public function itCreatesAnIncomeSource(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/budget/income', ['label' => 'Salary', 'meta' => 'Paid 28th', 'amount' => '2480'])
            ->assertRedirect()
            ->assertSessionHas('toast', 'Salary added');

        $this->assertDatabaseHas('income_sources', [
            'household_id' => $user->household_id,
            'label' => 'Salary',
            'amount_pence' => 248000,
        ]);
    }

    #[Test]
    public function itUpdatesAnIncomeSource(): void
    {
        $user = User::factory()->create();
        $income = IncomeSource::factory()->create(['household_id' => $user->household_id, 'label' => 'Salary']);

        $this->actingAs($user)
            ->patch("/budget/income/{$income->id}", ['label' => 'Sarah — salary', 'amount' => '2500'])
            ->assertRedirect()
            ->assertSessionHas('toast', 'Sarah — salary updated');

        $this->assertSame('Sarah — salary', $income->refresh()->label);
    }

    #[Test]
    public function itDoesNotUpdateAnotherHouseholdsIncomeSource(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $income = IncomeSource::factory()->create(['household_id' => $stranger->household_id, 'label' => 'Salary']);

        $this->actingAs($user)
            ->patch("/budget/income/{$income->id}", ['label' => 'Hijacked', 'amount' => '10'])
            ->assertNotFound();

        $this->assertSame('Salary', $income->refresh()->label);
    }

    #[Test]
    public function itDeletesAnIncomeSource(): void
    {
        $user = User::factory()->create();
        $income = IncomeSource::factory()->create(['household_id' => $user->household_id, 'label' => 'Airbnb']);

        $this->actingAs($user)
            ->delete("/budget/income/{$income->id}")
            ->assertRedirect()
            ->assertSessionHas('toast', 'Airbnb removed');

        $this->assertDatabaseMissing('income_sources', ['id' => $income->id]);
    }

    #[Test]
    public function itDoesNotDeleteAnotherHouseholdsIncomeSource(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $income = IncomeSource::factory()->create(['household_id' => $stranger->household_id]);

        $this->actingAs($user)
            ->delete("/budget/income/{$income->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('income_sources', ['id' => $income->id]);
    }
}
