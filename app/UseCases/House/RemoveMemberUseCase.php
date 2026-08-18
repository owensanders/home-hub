<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Contracts\Repositories\CalendarEventRepositoryInterface;
use App\Contracts\Repositories\ChoreRepositoryInterface;
use App\Contracts\Repositories\HouseholdRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\HouseholdRole;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RemoveMemberUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly ChoreRepositoryInterface $chores,
        private readonly CalendarEventRepositoryInterface $events,
        private readonly HouseholdRepositoryInterface $households,
    ) {}

    /**
     * Covers both removing an active member and cancelling a pending invite.
     * Chores assigned to them and events they were the only attendee of are
     * deleted with them. Planned meals they were cooking, and events with
     * other attendees, are left in place — the `cook_id`/attendee-pivot
     * foreign keys null/detach those automatically when the user row goes.
     *
     * @throws ValidationException
     */
    public function execute(User $member): void
    {
        if ($member->role === HouseholdRole::Owner && $this->isSoleOwner($member)) {
            throw ValidationException::withMessages(['member' => "You're the only owner — promote someone else first."]);
        }

        $this->chores->deleteAssignedTo($member);
        $this->events->deleteSoleAttendeeEventsFor($member);
        $this->users->delete($member);
    }

    private function isSoleOwner(User $member): bool
    {
        return $this->households->members($member->household)
            ->where('role', HouseholdRole::Owner)
            ->where('id', '!=', $member->id)
            ->isEmpty();
    }
}
