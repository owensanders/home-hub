<?php

declare(strict_types=1);

namespace App\UseCases\Auth;

use Illuminate\Support\Facades\Password;

class SendPasswordResetLinkUseCase
{
    /** @return string One of the `Password::` status constants. */
    public function execute(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }
}
