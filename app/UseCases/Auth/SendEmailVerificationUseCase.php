<?php

declare(strict_types=1);

namespace App\UseCases\Auth;

use App\Models\User;

class SendEmailVerificationUseCase
{
    /** False when the address is already verified, so no notification was sent. */
    public function execute(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->sendEmailVerificationNotification();

        return true;
    }
}
