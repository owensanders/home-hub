<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Contracts\Repositories\HouseholdRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\HouseholdRole;
use App\Enums\Palette;
use App\Models\Household;
use App\Models\User;

class CreateHouseholdUseCase
{
    public function __construct(
        private readonly HouseholdRepositoryInterface $households,
        private readonly UserRepositoryInterface $users,
        private readonly InviteMemberUseCase $inviteMember,
    ) {}

    /**
     * `size` is collected for the wizard's copy but has nowhere to live yet —
     * ponytail: not persisted, add a column when a feature actually reads it.
     *
     * @param  array{name: string, size?: string|null, invites?: list<array{email: string, role: string}>}  $attributes
     */
    public function execute(User $owner, array $attributes): Household
    {
        $household = $this->households->create([
            'name' => trim($attributes['name']),
            'trial_ends_at' => now()->addDays(config('plans.trial_days')),
        ]);

        $this->users->update($owner, [
            'current_household_id' => $household->id,
            'initials' => mb_strtoupper(mb_substr(trim($owner->name), 0, 2)),
            'colour' => Palette::cases()[0],
        ]);

        $owner->households()->attach($household->id, ['role' => HouseholdRole::Owner, 'pending' => false]);

        foreach ($attributes['invites'] ?? [] as $invite) {
            $this->inviteMember->execute($household, [
                'email' => $invite['email'],
                'role' => $invite['role'],
            ]);
        }

        return $household;
    }
}
