<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Contracts\Repositories\HouseholdRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\HouseholdRole;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ChangeMemberRoleUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly HouseholdRepositoryInterface $households,
    ) {}

    /** @throws ValidationException */
    public function execute(User $member, HouseholdRole $role): User
    {
        if ($member->role === HouseholdRole::Owner && $role !== HouseholdRole::Owner && $this->isSoleOwner($member)) {
            throw ValidationException::withMessages(['role' => "You're the only owner — promote someone else first."]);
        }

        return $this->users->update($member, ['role' => $role]);
    }

    private function isSoleOwner(User $member): bool
    {
        return $this->households->members($member->household)
            ->where('role', HouseholdRole::Owner)
            ->where('id', '!=', $member->id)
            ->isEmpty();
    }
}
