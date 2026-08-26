<?php

declare(strict_types=1);

namespace App\UseCases\Auth;

use App\Contracts\Repositories\HouseholdRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\HouseholdRole;
use App\Enums\Palette;
use App\Models\Household;
use App\Models\User;

class JoinHouseholdFromInviteUseCase
{
    public function __construct(
        private readonly HouseholdRepositoryInterface $households,
        private readonly UserRepositoryInterface $users,
    ) {}

    public function execute(User $user, Household $household, HouseholdRole $role): void
    {
        $colours = Palette::cases();
        $colour = $colours[$this->households->members($household)->count() % count($colours)];

        $this->users->update($user, [
            'current_household_id' => $household->id,
            'initials' => mb_strtoupper(mb_substr(trim($user->name), 0, 2)),
            'colour' => $colour,
        ]);

        $user->households()->attach($household->id, [
            'role' => $role,
            'pending' => false,
            'pending_reason' => null,
        ]);
    }
}
