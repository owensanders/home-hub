<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Str;

class UserRepository implements UserRepositoryInterface
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): User
    {
        return User::create($attributes);
    }

    /** @param array<string, mixed> $attributes */
    public function update(User $user, array $attributes): User
    {
        $user->update($attributes);

        return $user->refresh();
    }

    /** Persists attributes already set on the model, guarded ones included. */
    public function save(User $user): User
    {
        $user->save();

        return $user->refresh();
    }

    /** Already-hashed password in — hashing is the use case's job. */
    public function setPassword(User $user, string $hashedPassword): User
    {
        // forceFill, because `password` is hashed by a cast and the reset flow also
        // rotates the remember token.
        $user->forceFill([
            'password' => $hashedPassword,
            'remember_token' => Str::random(60),
        ])->save();

        return $user->refresh();
    }

    public function markEmailAsVerified(User $user): bool
    {
        return $user->markEmailAsVerified();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
