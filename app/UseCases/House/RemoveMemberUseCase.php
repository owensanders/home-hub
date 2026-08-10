<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Contracts\Repositories\CalendarEventRepositoryInterface;
use App\Contracts\Repositories\ChoreRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

class RemoveMemberUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly ChoreRepositoryInterface $chores,
        private readonly CalendarEventRepositoryInterface $events,
    ) {}

    /**
     * Covers both removing an active member and cancelling a pending invite.
     * Chores assigned to them and events they were the only attendee of are
     * deleted with them. Planned meals they were cooking, and events with
     * other attendees, are left in place — the `cook_id`/attendee-pivot
     * foreign keys null/detach those automatically when the user row goes.
     */
    public function execute(User $member): void
    {
        $this->chores->deleteAssignedTo($member);
        $this->events->deleteSoleAttendeeEventsFor($member);
        $this->users->delete($member);
    }
}
