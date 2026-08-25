<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Contracts\Repositories\HouseholdRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\HouseholdRole;
use App\Enums\Palette;
use App\Models\Household;
use App\Models\User;
use Illuminate\Support\Str;

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
     * `plan`/`cycle` are handled separately by the caller once the household
     * exists (a household must exist before it can be Billable).
     *
     * @param  array{name: string, address?: string|null, size?: string|null, plan?: string|null, cycle?: string|null, invites?: list<array{email: string, role: string}>}  $attributes
     */
    public function execute(User $owner, array $attributes): Household
    {
        $household = $this->households->create([
            'name' => trim($attributes['name']),
            'address' => $attributes['address'] ?? null,
        ]);

        $this->users->update($owner, [
            'household_id' => $household->id,
            'role' => HouseholdRole::Owner,
            'initials' => mb_strtoupper(mb_substr(trim($owner->name), 0, 2)),
            'colour' => Palette::cases()[0],
        ]);

        foreach ($attributes['invites'] ?? [] as $invite) {
            $this->inviteMember->execute($household, [
                'name' => Str::before($invite['email'], '@'),
                'email' => $invite['email'],
                'role' => $invite['role'],
            ]);
        }

        return $household;
    }
}
