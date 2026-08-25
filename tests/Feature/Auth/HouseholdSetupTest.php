<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\HouseholdRole;
use App\Enums\PendingReason;
use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HouseholdSetupTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function aHouseholdlessUserCanCreateAHouseholdAndBecomesOwner(): void
    {
        $user = User::factory()->create(['household_id' => null]);

        $response = $this->actingAs($user)->post('/household/setup', [
            'name' => 'The Parker household',
            'address' => '14 Elmgrove Road, Bristol',
            'size' => '4 people',
            'invites' => [
                ['email' => 'james@parkerhouse.co.uk', 'role' => 'adult'],
            ],
        ]);

        $response->assertRedirect(route('household.done', absolute: false));

        $user->refresh();
        $this->assertNotNull($user->household_id);
        $this->assertSame(HouseholdRole::Owner, $user->role);

        $household = $user->household;
        $this->assertSame('The Parker household', $household->name);
        $this->assertNotNull($household->join_code);

        $this->assertDatabaseHas('users', [
            'email' => 'james@parkerhouse.co.uk',
            'household_id' => $household->id,
            'pending' => true,
        ]);
    }

    #[Test]
    public function creatingAHouseholdOnTheFreePlanDoesNotContactStripe(): void
    {
        $user = User::factory()->create(['household_id' => null]);

        $response = $this->actingAs($user)->post('/household/setup', [
            'name' => 'The Parker household',
            'plan' => 'free',
            'cycle' => 'monthly',
        ]);

        $response->assertRedirect(route('household.done', absolute: false));
        $this->assertNotNull($user->refresh()->household_id);
    }

    #[Test]
    public function anUnknownPlanSlugFailsValidation(): void
    {
        $user = User::factory()->create(['household_id' => null]);

        $response = $this->actingAs($user)->post('/household/setup', [
            'name' => 'The Parker household',
            'plan' => 'ultra',
            'cycle' => 'monthly',
        ]);

        $response->assertSessionHasErrors('plan');
        $this->assertNull($user->refresh()->household_id);
    }

    #[Test]
    public function everyHouseholdGetsAJoinCodeRegardlessOfHowItWasCreated(): void
    {
        $household = Household::factory()->create();

        $this->assertNotNull($household->join_code);
    }

    #[Test]
    public function aHouseholdlessUserCanJoinAHouseholdWithAValidCode(): void
    {
        $household = Household::factory()->create(['join_code' => 'PARKER-4Q72KD']);
        $user = User::factory()->create(['household_id' => null]);

        $response = $this->actingAs($user)->post('/household/join', [
            'code' => 'parker-4q72kd',
        ]);

        $response->assertRedirect(route('household.done', absolute: false));

        $user->refresh();
        $this->assertSame($household->id, $user->household_id);
        $this->assertSame(HouseholdRole::Adult, $user->role);
        $this->assertTrue($user->pending);
        $this->assertSame(PendingReason::Requested, $user->pending_reason);
    }

    #[Test]
    public function joiningFailsWhenTheHouseholdHasTurnedTheCodeOff(): void
    {
        $household = Household::factory()->create(['join_code' => 'PARKER-4Q72KD', 'join_code_enabled' => false]);
        $user = User::factory()->create(['household_id' => null]);

        $response = $this->actingAs($user)->post('/household/join', [
            'code' => 'parker-4q72kd',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertNull($user->refresh()->household_id);
    }

    #[Test]
    public function joiningWithAnUnknownCodeFailsValidation(): void
    {
        $user = User::factory()->create(['household_id' => null]);

        $response = $this->actingAs($user)->post('/household/join', [
            'code' => 'NOT-A-REAL-CODE',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertNull($user->refresh()->household_id);
    }

    #[Test]
    public function aUserWhoAlreadyHasAHouseholdIsRedirectedAwayFromSetup(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/household/setup')->assertRedirect(route('dashboard', absolute: false));
    }

    #[Test]
    public function aHouseholdlessUserIsRedirectedToSetupInsteadOfSeeingTheDashboard(): void
    {
        $user = User::factory()->create(['household_id' => null]);

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('household.setup', absolute: false));
    }

    #[Test]
    public function aPendingMemberIsRedirectedAwayFromTheDashboardUntilApproved(): void
    {
        $household = Household::factory()->create();
        $user = User::factory()->create(['household_id' => $household->id, 'pending' => true]);

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('household.done', absolute: false));
    }

    #[Test]
    public function aPendingMemberVisitingSetupIsSentToTheWaitingScreenNotDashboard(): void
    {
        $household = Household::factory()->create();
        $user = User::factory()->create(['household_id' => $household->id, 'pending' => true]);

        $this->actingAs($user)->get('/household/setup')->assertRedirect(route('household.done', absolute: false));
    }

    #[Test]
    public function thePendingMemberSeesJoinCopyOnTheWaitingScreenEvenWithoutFreshFlashData(): void
    {
        $household = Household::factory()->create(['name' => 'The Parker household']);
        $user = User::factory()->create(['household_id' => $household->id, 'pending' => true]);

        $this->actingAs($user)
            ->get('/household/done')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('auth/HouseholdDone')
                ->where('mode', 'join')
                ->where('householdName', 'The Parker household')
                ->where('pending', true));
    }

    #[Test]
    public function theOwnerIsNotMarkedPendingOnTheDoneScreen(): void
    {
        $household = Household::factory()->create();
        $user = User::factory()->create(['household_id' => $household->id, 'role' => HouseholdRole::Owner, 'pending' => false]);

        $this->actingAs($user)
            ->get('/household/done')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('auth/HouseholdDone')
                ->where('pending', false));
    }
}
