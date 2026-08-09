<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ChoreStatus;
use App\Models\Chore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChoreBoardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itRendersAColumnPerStatusAndAScorePerMember(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create(['household_id' => $user->household_id]);

        Chore::factory()->count(2)->create([
            'household_id' => $user->household_id,
            'assigned_to' => $partner->id,
            'status' => ChoreStatus::Done,
        ]);
        Chore::factory()->create([
            'household_id' => $user->household_id,
            'assigned_to' => $partner->id,
            'status' => ChoreStatus::Upcoming,
        ]);

        $this->actingAs($user)
            ->get('/chores')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Chores')
                ->count('columns', 4)
                ->where('columns.0.status', 'today')
                ->where('columns.1.status', 'upcoming')
                ->where('columns.1.count', 1)
                ->where('columns.2.status', 'done')
                ->where('columns.2.count', 2)
                ->count('scores', 2)
                ->where('scores.1.done', 2)
                ->where('scores.1.total', 3)
                ->where('scores.1.percentage', 67)
            );
    }

    #[Test]
    public function tickingAChoreMarksItDoneAndUntickingSendsItBackToToday(): void
    {
        $user = User::factory()->create();
        $chore = Chore::factory()->create([
            'household_id' => $user->household_id,
            'status' => ChoreStatus::Upcoming,
        ]);

        $this->actingAs($user)->patch("/chores/{$chore->id}/toggle")->assertRedirect();
        $this->assertSame(ChoreStatus::Done, $chore->refresh()->status);

        $this->actingAs($user)->patch("/chores/{$chore->id}/toggle")->assertRedirect();
        $this->assertSame(ChoreStatus::Today, $chore->refresh()->status);
    }

    #[Test]
    public function itMovesAChoreToAnotherColumn(): void
    {
        $user = User::factory()->create();
        $chore = Chore::factory()->create(['household_id' => $user->household_id]);

        $this->actingAs($user)
            ->patch("/chores/{$chore->id}/move", ['status' => 'recurring'])
            ->assertRedirect()
            ->assertSessionHas('toast', 'Moved to Recurring');

        $this->assertSame(ChoreStatus::Recurring, $chore->refresh()->status);
    }

    #[Test]
    public function itRejectsAnUnknownColumn(): void
    {
        $user = User::factory()->create();
        $chore = Chore::factory()->create(['household_id' => $user->household_id]);

        $this->actingAs($user)
            ->patch("/chores/{$chore->id}/move", ['status' => 'someday'])
            ->assertSessionHasErrors('status');
    }

    #[Test]
    public function itDoesNotTouchAnotherHouseholdsChores(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $chore = Chore::factory()->create(['household_id' => $stranger->household_id]);

        $this->actingAs($user)->patch("/chores/{$chore->id}/toggle")->assertNotFound();
        $this->actingAs($user)->patch("/chores/{$chore->id}/move", ['status' => 'done'])->assertNotFound();

        $this->assertSame(ChoreStatus::Today, $chore->refresh()->status);
    }

    #[Test]
    public function itCreatesAChore(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/chores', [
                'name' => 'Clean the gutters',
                'status' => 'today',
                'due_label' => 'This weekend',
            ])
            ->assertRedirect()
            ->assertSessionHas('toast', 'Clean the gutters added');

        $this->assertDatabaseHas('chores', [
            'household_id' => $user->household_id,
            'name' => 'Clean the gutters',
            'status' => 'today',
        ]);
    }

    #[Test]
    public function itUpdatesAChore(): void
    {
        $user = User::factory()->create();
        $chore = Chore::factory()->create(['household_id' => $user->household_id, 'name' => 'Water the plants']);

        $this->actingAs($user)
            ->patch("/chores/{$chore->id}", [
                'name' => 'Water the houseplants',
                'status' => 'upcoming',
            ])
            ->assertRedirect()
            ->assertSessionHas('toast', 'Water the houseplants updated');

        $chore->refresh();
        $this->assertSame('Water the houseplants', $chore->name);
        $this->assertSame(ChoreStatus::Upcoming, $chore->status);
    }

    #[Test]
    public function itDoesNotUpdateAnotherHouseholdsChore(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $chore = Chore::factory()->create(['household_id' => $stranger->household_id, 'name' => 'Water the plants']);

        $this->actingAs($user)
            ->patch("/chores/{$chore->id}", ['name' => 'Hijacked', 'status' => 'today'])
            ->assertNotFound();

        $this->assertSame('Water the plants', $chore->refresh()->name);
    }

    #[Test]
    public function itDeletesAChore(): void
    {
        $user = User::factory()->create();
        $chore = Chore::factory()->create(['household_id' => $user->household_id, 'name' => 'Take out the bins']);

        $this->actingAs($user)
            ->delete("/chores/{$chore->id}")
            ->assertRedirect()
            ->assertSessionHas('toast', 'Take out the bins deleted');

        $this->assertDatabaseMissing('chores', ['id' => $chore->id]);
    }

    #[Test]
    public function itDoesNotDeleteAnotherHouseholdsChore(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $chore = Chore::factory()->create(['household_id' => $stranger->household_id]);

        $this->actingAs($user)
            ->delete("/chores/{$chore->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('chores', ['id' => $chore->id]);
    }
}
