<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BudgetCategory;
use App\Models\BudgetTransaction;
use App\Models\IncomeSource;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itRendersCategoriesIncomeAndTransactionsForTheMonth(): void
    {
        $user = User::factory()->create();
        $category = BudgetCategory::factory()->create([
            'household_id' => $user->household_id,
            'label' => 'Food & shopping',
            'budgeted_pence' => 50000,
            'spent_pence' => 12000,
        ]);
        IncomeSource::factory()->create(['household_id' => $user->household_id, 'amount_pence' => 200000]);
        BudgetTransaction::factory()->create([
            'household_id' => $user->household_id,
            'budget_category_id' => $category->id,
            'month' => CarbonImmutable::now()->startOfMonth(),
        ]);

        $this->actingAs($user)
            ->get('/budget')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Budget')
                ->count('categories', 1)
                ->count('income', 1)
                ->count('transactions', 1)
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
    public function itDeletesACategoryAndCascadesItsTransactions(): void
    {
        $user = User::factory()->create();
        $category = BudgetCategory::factory()->create(['household_id' => $user->household_id, 'label' => 'Subscriptions']);
        $transaction = BudgetTransaction::factory()->create([
            'household_id' => $user->household_id,
            'budget_category_id' => $category->id,
        ]);

        $this->actingAs($user)
            ->delete("/budget/categories/{$category->id}")
            ->assertRedirect()
            ->assertSessionHas('toast', 'Subscriptions removed');

        $this->assertDatabaseMissing('budget_categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('budget_transactions', ['id' => $transaction->id]);
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
    public function itLogsASpendAndIncrementsTheCategorysSpentTotal(): void
    {
        $user = User::factory()->create();
        $category = BudgetCategory::factory()->create(['household_id' => $user->household_id, 'spent_pence' => 1000]);

        $this->actingAs($user)
            ->post('/budget/transactions', [
                'budget_category_id' => $category->id,
                'label' => 'Petrol',
                'amount' => '48.20',
            ])
            ->assertRedirect()
            ->assertSessionHas('toast', 'Logged Petrol');

        $this->assertDatabaseHas('budget_transactions', [
            'household_id' => $user->household_id,
            'budget_category_id' => $category->id,
            'label' => 'Petrol',
            'amount_pence' => 4820,
        ]);
        $this->assertSame(5820, $category->refresh()->spent_pence);
    }

    #[Test]
    public function itRefusesASpendAgainstAnotherHouseholdsCategory(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $category = BudgetCategory::factory()->create(['household_id' => $stranger->household_id]);

        $this->actingAs($user)
            ->post('/budget/transactions', ['budget_category_id' => $category->id, 'label' => 'Petrol', 'amount' => '10'])
            ->assertSessionHasErrors('budget_category_id');

        $this->assertDatabaseCount('budget_transactions', 0);
    }

    #[Test]
    public function itReassignsATransactionAndMovesBothCategoryTotals(): void
    {
        $user = User::factory()->create();
        $from = BudgetCategory::factory()->create(['household_id' => $user->household_id, 'spent_pence' => 5000]);
        $to = BudgetCategory::factory()->create(['household_id' => $user->household_id, 'spent_pence' => 2000]);
        $transaction = BudgetTransaction::factory()->create([
            'household_id' => $user->household_id,
            'budget_category_id' => $from->id,
            'amount_pence' => 3000,
        ]);

        $this->actingAs($user)
            ->patch("/budget/transactions/{$transaction->id}", ['budget_category_id' => $to->id])
            ->assertRedirect();

        $this->assertSame($to->id, $transaction->refresh()->budget_category_id);
        $this->assertSame(2000, $from->refresh()->spent_pence);
        $this->assertSame(5000, $to->refresh()->spent_pence);
    }

    #[Test]
    public function itDoesNotReassignAnotherHouseholdsTransaction(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $category = BudgetCategory::factory()->create(['household_id' => $user->household_id]);
        $transaction = BudgetTransaction::factory()->create(['household_id' => $stranger->household_id]);

        $this->actingAs($user)
            ->patch("/budget/transactions/{$transaction->id}", ['budget_category_id' => $category->id])
            ->assertNotFound();
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
