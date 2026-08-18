<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    #[Test]
    public function itPrefillsTheEmailFromTheLandingPageCta(): void
    {
        $this->get('/register?email=you@household.co.uk')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('email', 'you@household.co.uk'));
    }

    #[Test]
    public function itIgnoresANonStringEmailQueryParameter(): void
    {
        $this->get('/register?email[]=you@household.co.uk')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('email', ''));
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'a-very-strong-password',
            'terms' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('household.setup', absolute: false));
    }
}
