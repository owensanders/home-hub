<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Data\HouseholdOptionData;
use App\Models\Household;
use App\Models\User;

class GetHouseholdOptionsUseCase
{
    /** @return list<HouseholdOptionData> */
    public function execute(User $user): array
    {
        $households = $user->households()->withCount('members')->with('members')->get();

        return $households->map(fn (Household $household) => new HouseholdOptionData(
            id: $household->id,
            name: $household->name,
            role: $household->pivot->role->value,
            roleLabel: $household->pivot->role->label(),
            memberCount: $household->members_count,
            memberAvatars: $household->members->take(4)
                ->map(fn (User $member) => [
                    'initials' => $member->initials ?? mb_strtoupper(mb_substr($member->name, 0, 2)),
                    'colour' => $member->colour->cssVar(),
                ])
                ->values()
                ->all(),
        ))->values()->all();
    }
}
