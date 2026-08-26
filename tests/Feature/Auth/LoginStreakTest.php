<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Household;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginStreakTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itStartsTheStreakOnAUsersFirstLogin(): void
    {
        Date::setTestNow(CarbonImmutable::parse('2026-08-26'));

        $household = Household::factory()->create(['streak_days' => 0, 'streak_last_active_date' => null]);
        $user = User::factory()->create(['household_id' => $household->id]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $household->refresh();
        $this->assertSame(1, $household->streak_days);
        $this->assertTrue($household->streak_last_active_date->isSameDay(CarbonImmutable::parse('2026-08-26')));
    }

    #[Test]
    public function itIncrementsTheStreakOnConsecutiveDayLogins(): void
    {
        $household = Household::factory()->create([
            'streak_days' => 5,
            'streak_last_active_date' => CarbonImmutable::parse('2026-08-25'),
        ]);
        $user = User::factory()->create(['household_id' => $household->id]);

        Date::setTestNow(CarbonImmutable::parse('2026-08-26'));
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $household->refresh();
        $this->assertSame(6, $household->streak_days);
    }

    #[Test]
    public function itResetsTheStreakAfterAMissedDay(): void
    {
        $household = Household::factory()->create([
            'streak_days' => 5,
            'streak_last_active_date' => CarbonImmutable::parse('2026-08-20'),
        ]);
        $user = User::factory()->create(['household_id' => $household->id]);

        Date::setTestNow(CarbonImmutable::parse('2026-08-26'));
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $household->refresh();
        $this->assertSame(1, $household->streak_days);
    }

    #[Test]
    public function itDoesNotDoubleCountASecondLoginOnTheSameDay(): void
    {
        $household = Household::factory()->create([
            'streak_days' => 5,
            'streak_last_active_date' => CarbonImmutable::parse('2026-08-26'),
        ]);
        $user = User::factory()->create(['household_id' => $household->id]);

        Date::setTestNow(CarbonImmutable::parse('2026-08-26'));
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $household->refresh();
        $this->assertSame(5, $household->streak_days);
    }
}
