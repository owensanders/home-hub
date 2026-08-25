<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Household;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Subscription;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HouseholdPlanTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function anUnsubscribedHouseholdIsOnTheFreePlan(): void
    {
        $household = Household::factory()->create();

        $this->assertSame('free', $household->planSlug());
    }

    #[Test]
    public function anActiveSubscriptionResolvesToItsMatchingPlan(): void
    {
        config(['plans.home.stripe_price.monthly' => 'price_home_monthly_test']);

        $household = Household::factory()->create();
        Subscription::create([
            'household_id' => $household->id,
            'type' => 'default',
            'stripe_id' => 'sub_test123',
            'stripe_status' => 'active',
            'stripe_price' => 'price_home_monthly_test',
        ]);

        $this->assertSame('home', $household->planSlug());
    }

    #[Test]
    public function anImmediatelyCancelledSubscriptionResolvesToFreeRightAway(): void
    {
        $household = Household::factory()->create();
        Subscription::create([
            'household_id' => $household->id,
            'type' => 'default',
            'stripe_id' => 'sub_test456',
            'stripe_status' => 'active',
            'stripe_price' => 'price_home_monthly_test',
            'ends_at' => now()->subMinute(),
        ]);

        $this->assertSame('free', $household->planSlug());
    }
}
