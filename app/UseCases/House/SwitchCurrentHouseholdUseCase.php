<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

class SwitchCurrentHouseholdUseCase
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function execute(User $user, int $householdId): void
    {
        abort_unless($user->households()->where('households.id', $householdId)->exists(), 403);

        $this->users->update($user, ['current_household_id' => $householdId]);
    }
}
