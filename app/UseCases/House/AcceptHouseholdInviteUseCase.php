<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Models\Household;
use App\Models\User;

class AcceptHouseholdInviteUseCase
{
    public function __construct(
        private readonly ApproveMemberUseCase $approveMember,
    ) {}

    public function execute(User $user, Household $household): void
    {
        $pivot = $user->households()->where('households.id', $household->id)->first()?->pivot;

        abort_if($pivot === null || $pivot->pending !== true, 404);

        $this->approveMember->execute($user, $household);
    }
}
