<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\HouseholdRole;
use App\Models\User;

class ChangeMemberRoleUseCase
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function execute(User $member, HouseholdRole $role): User
    {
        return $this->users->update($member, ['role' => $role]);
    }
}
