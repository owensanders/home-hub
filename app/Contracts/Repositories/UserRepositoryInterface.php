<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): User;

    /** @param array<string, mixed> $attributes */
    public function update(User $user, array $attributes): User;

    /** Persists attributes already set on the model, guarded ones included. */
    public function save(User $user): User;

    /** Already-hashed password in — hashing is the use case's job. */
    public function setPassword(User $user, string $hashedPassword): User;

    public function markEmailAsVerified(User $user): bool;

    public function delete(User $user): void;
}
