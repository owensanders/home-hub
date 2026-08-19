<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Enums\HouseholdRole;
use App\Models\Chore;
use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itDeletesAUserWithNoHousehold(): void
    {
        $user = User::factory()->create(['household_id' => null, 'role' => HouseholdRole::Adult]);

        $this->actingAs($user)
            ->delete('/settings/profile', ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Test]
    public function itDeletesANonOwnerMemberWithoutTouchingTheHousehold(): void
    {
        $household = Household::factory()->create();
        User::factory()->create(['household_id' => $household->id, 'role' => HouseholdRole::Owner]);
        $member = User::factory()->create(['household_id' => $household->id, 'role' => HouseholdRole::Adult]);

        $this->actingAs($member)
            ->delete('/settings/profile', ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
        $this->assertDatabaseHas('households', ['id' => $household->id]);
    }

    #[Test]
    public function itDeletesAnOwnerWhenAnotherOwnerRemains(): void
    {
        $household = Household::factory()->create();
        $owner = User::factory()->create(['household_id' => $household->id, 'role' => HouseholdRole::Owner]);
        User::factory()->create(['household_id' => $household->id, 'role' => HouseholdRole::Owner]);

        $this->actingAs($owner)
            ->delete('/settings/profile', ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $owner->id]);
        $this->assertDatabaseHas('households', ['id' => $household->id]);
    }

    #[Test]
    public function itDeletesTheHouseholdWhenTheSoleOwnerHasNoOtherMembers(): void
    {
        $household = Household::factory()->create();
        $owner = User::factory()->create(['household_id' => $household->id, 'role' => HouseholdRole::Owner]);

        $this->actingAs($owner)
            ->delete('/settings/profile', ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $owner->id]);
        $this->assertDatabaseMissing('households', ['id' => $household->id]);
    }

    #[Test]
    public function itBlocksTheSoleOwnerWithOtherMembersWithoutConfirmation(): void
    {
        $household = Household::factory()->create();
        $owner = User::factory()->create(['household_id' => $household->id, 'role' => HouseholdRole::Owner]);
        $member = User::factory()->create(['household_id' => $household->id, 'role' => HouseholdRole::Adult]);

        $this->actingAs($owner)
            ->delete('/settings/profile', ['password' => 'password'])
            ->assertSessionHasErrors('household');

        $this->assertDatabaseHas('users', ['id' => $owner->id]);
        $this->assertDatabaseHas('users', ['id' => $member->id]);
        $this->assertDatabaseHas('households', ['id' => $household->id]);
    }

    #[Test]
    public function itDeletesTheHouseholdAndUnlinksOtherMembersWhenConfirmed(): void
    {
        $household = Household::factory()->create();
        $owner = User::factory()->create(['household_id' => $household->id, 'role' => HouseholdRole::Owner]);
        $member = User::factory()->create(['household_id' => $household->id, 'role' => HouseholdRole::Adult]);
        $chore = Chore::factory()->create(['household_id' => $household->id]);

        $this->actingAs($owner)
            ->delete('/settings/profile', [
                'password' => 'password',
                'confirm_household_deletion' => true,
            ])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $owner->id]);
        $this->assertDatabaseMissing('households', ['id' => $household->id]);
        $this->assertDatabaseMissing('chores', ['id' => $chore->id]);

        $this->assertDatabaseHas('users', ['id' => $member->id, 'household_id' => null]);
    }
}
