<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\UseCases\Auth\VerifyEmailUseCase;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request, VerifyEmailUseCase $verify): RedirectResponse
    {
        $verify->execute($request->user());

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
