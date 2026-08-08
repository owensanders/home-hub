<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CalendarEventRequest;
use App\Models\CalendarEvent;
use App\Traits\ResolvesHouseholdTrait;
use App\UseCases\Calendar\AddCalendarEvent;
use App\UseCases\Calendar\DeleteCalendarEvent;
use App\UseCases\Calendar\GetCalendarMonth;
use App\UseCases\Calendar\UpdateCalendarEvent;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    use ResolvesHouseholdTrait;

    public function index(Request $request, GetCalendarMonth $getCalendarMonth): Response
    {
        return Inertia::render('Calendar', [
            'calendar' => $getCalendarMonth->execute($this->household($request), $this->month($request)),
        ]);
    }

    public function store(CalendarEventRequest $request, AddCalendarEvent $add): RedirectResponse
    {
        $event = $add->execute(
            $this->household($request),
            $request->eventAttributes(),
            $request->attendeeIds(),
        );

        return back()->with('toast', "“{$event->title}” added");
    }

    public function update(CalendarEventRequest $request, CalendarEvent $event, UpdateCalendarEvent $update): RedirectResponse
    {
        $this->assertOwned($request, $event);

        $updated = $update->execute($event, $request->eventAttributes(), $request->attendeeIds());

        return back()->with('toast', "“{$updated->title}” updated");
    }

    public function destroy(Request $request, CalendarEvent $event, DeleteCalendarEvent $delete): RedirectResponse
    {
        $this->assertOwned($request, $event);

        $title = $event->title;
        $delete->execute($event);

        return back()->with('toast', "“{$title}” deleted");
    }

    /**
     * The month being viewed, from `?month=YYYY-MM`. Anything unparseable — a
     * hand-edited URL, a stale link — falls back to the current month.
     */
    private function month(Request $request): CarbonImmutable
    {
        $month = $request->query('month');

        if (! is_string($month)) {
            return CarbonImmutable::now();
        }

        $parsed = CarbonImmutable::canBeCreatedFromFormat($month, 'Y-m')
            ? CarbonImmutable::createFromFormat('Y-m', $month)
            : false;

        return $parsed instanceof CarbonImmutable ? $parsed : CarbonImmutable::now();
    }

    private function assertOwned(Request $request, CalendarEvent $event): void
    {
        abort_if($event->household_id !== $this->household($request)->id, 404);
    }
}
