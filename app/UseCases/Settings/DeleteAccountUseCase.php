<?php

declare(strict_types=1);

namespace App\UseCases\Settings;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DeleteAccountUseCase
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function execute(User $user): void
    {
        Auth::logout();

        $this->users->delete($user);

        session()->invalidate();
        session()->regenerateToken();
    }
}
