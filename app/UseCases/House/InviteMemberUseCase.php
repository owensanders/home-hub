<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Contracts\Repositories\HouseholdRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\HouseholdRole;
use App\Enums\Palette;
use App\Enums\PendingReason;
use App\Models\Household;
use App\Models\User;
use Illuminate\Support\Str;

class InviteMemberUseCase
{
    public function __construct(
        private readonly HouseholdRepositoryInterface $households,
        private readonly UserRepositoryInterface $users,
    ) {}

    /** @param  array{name: string, email: string, role: string}  $attributes */
    public function execute(Household $household, array $attributes): User
    {
        $name = trim($attributes['name']);
        $colours = Palette::cases();
        $colour = $colours[$this->households->members($household)->count() % count($colours)];

        // ponytail: invite is data-only — creates a pending User row, no email is
        // sent. Wire real delivery when this needs to leave the building.
        return $this->users->create([
            'household_id' => $household->id,
            'name' => $name,
            'initials' => mb_strtoupper(mb_substr($name, 0, 2)),
            'colour' => $colour,
            'role' => HouseholdRole::from($attributes['role']),
            'pending' => true,
            'pending_reason' => PendingReason::Invited,
            'email' => $attributes['email'],
            'password' => Str::random(40),
        ]);
    }
}
