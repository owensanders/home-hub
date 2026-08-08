<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CalendarEvent;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itRendersSixWeeksOfCellsForTheCurrentMonth(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/calendar')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Calendar')
                ->count('calendar.days', 42)
                ->count('calendar.weekdayLabels', 7)
                ->where('calendar.month', CarbonImmutable::now()->format('Y-m'))
                ->where('calendar.monthLabel', CarbonImmutable::now()->format('F Y'))
            );
    }

    #[Test]
    public function itMarksTodayAndGreysTheNeighbouringMonths(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/calendar')
            ->assertInertia(function ($page) {
                /** @var array<int, array<string, mixed>> $days */
                $days = $page->toArray()['props']['calendar']['days'];

                $today = CarbonImmutable::today()->toDateString();
                $todayCells = array_values(array_filter($days, fn (array $day) => $day['isToday'] === true));

                $this->assertCount(1, $todayCells);
                $this->assertSame($today, $todayCells[0]['date']);
                $this->assertTrue($todayCells[0]['isCurrentMonth']);

                // A 42-cell grid always straddles at least one neighbouring month.
                $outside = array_filter($days, fn (array $day) => $day['isCurrentMonth'] === false);
                $this->assertNotEmpty($outside);
            });
    }

    #[Test]
    public function itHonoursTheMonthQueryStringAndLinksEitherSide(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/calendar?month=2026-09')
            ->assertInertia(fn ($page) => $page
                ->where('calendar.month', '2026-09')
                ->where('calendar.monthLabel', 'September 2026')
                ->where('calendar.previousMonth', '2026-08')
                ->where('calendar.nextMonth', '2026-10')
            );
    }

    #[Test]
    public function itRejectsAMonthItCannotUnderstand(): void
    {
        $user = User::factory()->create();

        foreach (['2026-13', '2026-00', 'not-a-month', '2026-08-01'] as $month) {
            $this->actingAs($user)
                ->get("/calendar?month={$month}")
                ->assertRedirect('/calendar')
                ->assertSessionHasErrors('month');
        }
    }

    #[Test]
    public function itResolvesTheMonthEvenWhenTodayIsThe31st(): void
    {
        // Without a `!` in the date format PHP fills the missing day from today,
        // so on the 31st "2026-09" rolled over into October.
        CarbonImmutable::setTestNow('2026-08-31');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/calendar?month=2026-09')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('calendar.month', '2026-09')
                ->where('calendar.monthLabel', 'September 2026')
            );

        CarbonImmutable::setTestNow();
    }

    #[Test]
    public function anEmptyMonthFallsBackToThisOne(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/calendar?month=')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('calendar.month', CarbonImmutable::now()->format('Y-m')));
    }

    #[Test]
    public function tickingAllDayDropsAStaleEndTimeInsteadOfFailingValidation(): void
    {
        $user = User::factory()->create();

        $event = CalendarEvent::factory()->create([
            'household_id' => $user->household_id,
            'starts_at' => CarbonImmutable::parse('2026-08-12 18:00'),
            'ends_at' => CarbonImmutable::parse('2026-08-12 19:30'),
        ]);

        // The hidden end time is still in the payload and is now before the start.
        $this->actingAs($user)
            ->patch("/calendar/events/{$event->id}", [
                'title' => 'All-day now',
                'starts_at' => '2026-08-12T20:00',
                'ends_at' => '2026-08-12T19:30',
                'is_all_day' => true,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $event->refresh();

        $this->assertTrue($event->is_all_day);
        $this->assertNull($event->ends_at);
    }

    #[Test]
    public function itPlacesAnEventInTheCellForItsDate(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create(['household_id' => $user->household_id]);

        $event = CalendarEvent::factory()->create([
            'household_id' => $user->household_id,
            'title' => 'Football practice',
            'starts_at' => CarbonImmutable::today()->setTime(18, 0),
            'ends_at' => CarbonImmutable::today()->setTime(19, 30),
        ]);
        $event->attendees()->sync([$partner->id]);

        $this->actingAs($user)
            ->get('/calendar')
            ->assertInertia(function ($page) use ($partner) {
                /** @var array<int, array<string, mixed>> $days */
                $days = $page->toArray()['props']['calendar']['days'];
                $today = collect($days)->firstWhere('isToday', true);

                $this->assertCount(1, $today['events']);
                $this->assertSame('Football practice', $today['events'][0]['title']);
                $this->assertSame('18:00–19:30', $today['events'][0]['time']);
                $this->assertSame($partner->name, $today['events'][0]['attendees'][0]['name']);
            });
    }

    #[Test]
    public function anEventWithNoAttendeesIsForTheWholeHouse(): void
    {
        $user = User::factory()->create();

        CalendarEvent::factory()->allDay()->create([
            'household_id' => $user->household_id,
            'title' => 'Bank holiday',
        ]);

        $this->actingAs($user)
            ->get('/calendar')
            ->assertInertia(function ($page) {
                /** @var array<int, array<string, mixed>> $days */
                $days = $page->toArray()['props']['calendar']['days'];
                $today = collect($days)->firstWhere('isToday', true);

                $this->assertSame('Everyone', $today['events'][0]['who']);
                $this->assertSame('All day', $today['events'][0]['time']);
                $this->assertSame([], $today['events'][0]['attendees']);
            });
    }

    #[Test]
    public function itAddsAnEventForTheChosenMembers(): void
    {
        $user = User::factory()->create();
        $child = User::factory()->create(['household_id' => $user->household_id]);

        $this->actingAs($user)
            ->post('/calendar/events', [
                'title' => '  Football practice  ',
                'starts_at' => '2026-08-12T18:00',
                'ends_at' => '2026-08-12T19:30',
                'location' => 'Beckett Park',
                'colour' => 'sky',
                'attendees' => [$child->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('toast', '“Football practice” added');

        $event = CalendarEvent::query()->firstOrFail();

        $this->assertSame('Football practice', $event->title);
        $this->assertSame($user->household_id, $event->household_id);
        $this->assertSame('Beckett Park', $event->location);
        $this->assertSame([$child->id], $event->attendees->pluck('id')->all());
    }

    #[Test]
    public function anAllDayEventDropsItsEndTime(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/calendar/events', [
                'title' => "Grandma's birthday",
                'starts_at' => '2026-08-09T00:00',
                'ends_at' => '2026-08-09T23:00',
                'is_all_day' => true,
            ])
            ->assertRedirect();

        $this->assertNull(CalendarEvent::query()->firstOrFail()->ends_at);
    }

    #[Test]
    public function itUpdatesAnEventAndReplacesItsAttendees(): void
    {
        $user = User::factory()->create();
        $child = User::factory()->create(['household_id' => $user->household_id]);

        $event = CalendarEvent::factory()->create(['household_id' => $user->household_id]);
        $event->attendees()->sync([$user->id]);

        $this->actingAs($user)
            ->patch("/calendar/events/{$event->id}", [
                'title' => 'Swim club',
                'starts_at' => '2026-08-11T16:00',
                'attendees' => [$child->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('toast', '“Swim club” updated');

        $event->refresh();

        $this->assertSame('Swim club', $event->title);
        $this->assertSame([$child->id], $event->attendees->pluck('id')->all());
    }

    #[Test]
    public function itDeletesAnEvent(): void
    {
        $user = User::factory()->create();
        $event = CalendarEvent::factory()->create([
            'household_id' => $user->household_id,
            'title' => 'Client call',
        ]);

        $this->actingAs($user)
            ->delete("/calendar/events/{$event->id}")
            ->assertRedirect()
            ->assertSessionHas('toast', '“Client call” deleted');

        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }

    #[Test]
    public function itRejectsAnEndTimeBeforeTheStart(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/calendar/events', [
                'title' => 'Backwards',
                'starts_at' => '2026-08-12T18:00',
                'ends_at' => '2026-08-12T17:00',
            ])
            ->assertSessionHasErrors('ends_at');

        $this->assertDatabaseCount('calendar_events', 0);
    }

    #[Test]
    public function itRefusesAnAttendeeFromAnotherHousehold(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($user)
            ->post('/calendar/events', [
                'title' => 'Not yours',
                'starts_at' => '2026-08-12T18:00',
                'attendees' => [$stranger->id],
            ])
            ->assertSessionHasErrors('attendees.0');

        $this->assertDatabaseCount('calendar_events', 0);
    }

    #[Test]
    public function itDoesNotTouchAnotherHouseholdsEvents(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        $event = CalendarEvent::factory()->create([
            'household_id' => $stranger->household_id,
            'title' => 'Theirs',
        ]);

        $this->actingAs($user)
            ->patch("/calendar/events/{$event->id}", ['title' => 'Mine now', 'starts_at' => '2026-08-12T18:00'])
            ->assertNotFound();

        $this->actingAs($user)
            ->delete("/calendar/events/{$event->id}")
            ->assertNotFound();

        $this->assertSame('Theirs', $event->refresh()->title);
    }

    #[Test]
    public function itOnlyShowsEventsFromTheUsersOwnHousehold(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        CalendarEvent::factory()->create(['household_id' => $stranger->household_id, 'title' => 'Theirs']);

        $this->actingAs($user)
            ->get('/calendar')
            ->assertInertia(function ($page) {
                /** @var array<int, array<string, mixed>> $days */
                $days = $page->toArray()['props']['calendar']['days'];

                $this->assertSame(0, collect($days)->sum(fn (array $day) => count($day['events'])));
            });
    }
}
