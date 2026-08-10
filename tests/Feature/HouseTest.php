<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\HouseholdRole;
use App\Models\CalendarEvent;
use App\Models\Chore;
use App\Models\Household;
use App\Models\PlannedMeal;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HouseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itRendersTheHouseholdAndItsMembers(): void
    {
        $household = Household::create([
            'name' => 'The Parkers', 'location' => 'Bristol', 'address' => '14 Elmgrove Road, Bristol', 'streak_days' => 12,
        ]);
        $owner = User::factory()->create(['household_id' => $household->id, 'role' => HouseholdRole::Owner]);
        User::factory()->create(['household_id' => $household->id, 'role' => HouseholdRole::Adult]);
        User::factory()->create(['household_id' => $household->id, 'role' => HouseholdRole::Adult, 'pending' => true]);

        $this->actingAs($owner)
            ->get('/house')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('House')
                ->where('house.houseName', 'The Parkers')
                ->where('house.houseAddress', '14 Elmgrove Road, Bristol')
                ->where('house.memberCount', 3)
                ->where('house.houseStats.0.value', '2')
                ->where('house.houseStats.1.value', '1')
                ->count('house.members', 3)
                ->count('house.roles', 4)
            );
    }

    #[Test]
    public function itInvitesAMember(): void
    {
        $owner = User::factory()->create(['role' => HouseholdRole::Owner]);

        $this->actingAs($owner)
            ->post('/house/invite', ['name' => 'Margaret Parker', 'email' => 'margaret@parkerhouse.co.uk', 'role' => 'adult'])
            ->assertRedirect()
            ->assertSessionHas('toast', 'Invite sent to margaret@parkerhouse.co.uk');

        $this->assertDatabaseHas('users', [
            'household_id' => $owner->household_id,
            'email' => 'margaret@parkerhouse.co.uk',
            'pending' => true,
        ]);
    }

    #[Test]
    public function itRejectsAnInviteWithMissingFields(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post('/house/invite', ['name' => '', 'email' => '', 'role' => 'adult'])
            ->assertSessionHasErrors(['name', 'email']);
    }

    #[Test]
    public function itChangesAMembersRole(): void
    {
        $owner = User::factory()->create(['role' => HouseholdRole::Owner]);
        $member = User::factory()->create(['household_id' => $owner->household_id, 'role' => HouseholdRole::Adult]);

        $this->actingAs($owner)
            ->patch("/house/members/{$member->id}/role", ['role' => 'teen'])
            ->assertRedirect()
            ->assertSessionHas('toast', "{$member->name} is now Teen");

        $this->assertSame(HouseholdRole::Teen, $member->refresh()->role);
    }

    #[Test]
    public function itRemovesAMember(): void
    {
        $owner = User::factory()->create(['role' => HouseholdRole::Owner]);
        $member = User::factory()->create(['household_id' => $owner->household_id, 'name' => 'James Parker']);

        $this->actingAs($owner)
            ->delete("/house/members/{$member->id}")
            ->assertRedirect()
            ->assertSessionHas('toast', 'James Parker removed from the household');

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
    }

    #[Test]
    public function itCancelsAPendingInvite(): void
    {
        $owner = User::factory()->create(['role' => HouseholdRole::Owner]);
        $invitee = User::factory()->create(['household_id' => $owner->household_id, 'name' => 'Margaret Parker', 'pending' => true]);

        $this->actingAs($owner)
            ->delete("/house/members/{$invitee->id}")
            ->assertRedirect()
            ->assertSessionHas('toast', 'Margaret Parker — invite cancelled');

        $this->assertDatabaseMissing('users', ['id' => $invitee->id]);
    }

    #[Test]
    public function itDeletesChoresAssignedToTheRemovedMember(): void
    {
        $owner = User::factory()->create(['role' => HouseholdRole::Owner]);
        $member = User::factory()->create(['household_id' => $owner->household_id]);
        $chore = Chore::factory()->create(['household_id' => $owner->household_id, 'assigned_to' => $member->id]);

        $this->actingAs($owner)->delete("/house/members/{$member->id}")->assertRedirect();

        $this->assertDatabaseMissing('chores', ['id' => $chore->id]);
    }

    #[Test]
    public function itDeletesAnEventWhenTheRemovedMemberWasItsOnlyAttendee(): void
    {
        $owner = User::factory()->create(['role' => HouseholdRole::Owner]);
        $member = User::factory()->create(['household_id' => $owner->household_id]);
        $event = CalendarEvent::factory()->create(['household_id' => $owner->household_id]);
        $event->attendees()->attach($member->id);

        $this->actingAs($owner)->delete("/house/members/{$member->id}")->assertRedirect();

        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }

    #[Test]
    public function itOnlyDetachesTheRemovedMemberFromAnEventWithOtherAttendees(): void
    {
        $owner = User::factory()->create(['role' => HouseholdRole::Owner]);
        $member = User::factory()->create(['household_id' => $owner->household_id]);
        $event = CalendarEvent::factory()->create(['household_id' => $owner->household_id]);
        $event->attendees()->attach([$member->id, $owner->id]);

        $this->actingAs($owner)->delete("/house/members/{$member->id}")->assertRedirect();

        $this->assertDatabaseHas('calendar_events', ['id' => $event->id]);
        $this->assertDatabaseMissing('calendar_event_user', ['calendar_event_id' => $event->id, 'user_id' => $member->id]);
        $this->assertDatabaseHas('calendar_event_user', ['calendar_event_id' => $event->id, 'user_id' => $owner->id]);
    }

    #[Test]
    public function itUnassignsPlannedMealsCookedByTheRemovedMember(): void
    {
        $owner = User::factory()->create(['role' => HouseholdRole::Owner]);
        $member = User::factory()->create(['household_id' => $owner->household_id]);
        $recipe = Recipe::factory()->create(['household_id' => $owner->household_id]);
        $meal = PlannedMeal::factory()->create([
            'household_id' => $owner->household_id, 'cook_id' => $member->id, 'recipe_id' => $recipe->id,
        ]);

        $this->actingAs($owner)->delete("/house/members/{$member->id}")->assertRedirect();

        $this->assertDatabaseHas('planned_meals', ['id' => $meal->id, 'cook_id' => null]);
    }

    #[Test]
    public function itTogglesAHouseholdSetting(): void
    {
        $owner = User::factory()->create(['role' => HouseholdRole::Owner]);

        $this->actingAs($owner)->patch('/house/settings', ['key' => 'approve'])->assertRedirect();

        $this->assertTrue((bool) $owner->household->refresh()->settings['approve']);

        $this->actingAs($owner)->patch('/house/settings', ['key' => 'approve'])->assertRedirect();

        $this->assertFalse((bool) $owner->household->refresh()->settings['approve']);
    }

    #[Test]
    public function itDoesNotTouchAnotherHouseholdsMembers(): void
    {
        $owner = User::factory()->create(['role' => HouseholdRole::Owner]);
        $stranger = User::factory()->create();

        $this->actingAs($owner)->patch("/house/members/{$stranger->id}/role", ['role' => 'teen'])->assertNotFound();
        $this->actingAs($owner)->delete("/house/members/{$stranger->id}")->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $stranger->id]);
    }
}
