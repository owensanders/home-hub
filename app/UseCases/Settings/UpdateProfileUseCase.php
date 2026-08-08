<?php

declare(strict_types=1);

namespace App\UseCases\Settings;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

class UpdateProfileUseCase
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    /** @param array<string, mixed> $attributes */
    public function execute(User $user, array $attributes): User
    {
        $user->fill($attributes);

        // Changing the address makes the old verification meaningless. Set directly
        // rather than through fill() — `email_verified_at` is not fillable.
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        return $this->users->save($user);
    }
}
