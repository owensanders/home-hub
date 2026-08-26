<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Models\Household;
use App\Models\User;

class ApproveMemberUseCase
{
    public function execute(User $member, Household $household): User
    {
        $member->households()->updateExistingPivot($household->id, [
            'pending' => false,
            'pending_reason' => null,
        ]);

        return $member->refresh();
    }
}
