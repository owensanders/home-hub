<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WelcomeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itRendersTheLandingPageForGuests(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Welcome')
                ->count('plans', 3)
                ->where('plans.0.slug', 'free'));
    }

    #[Test]
    public function itRendersTheLandingPageForAuthenticatedUsers(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Welcome'));
    }
}
