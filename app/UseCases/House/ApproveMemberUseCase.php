<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

class ApproveMemberUseCase
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function execute(User $member): User
    {
        return $this->users->update($member, ['pending' => false, 'pending_reason' => null]);
    }
}
