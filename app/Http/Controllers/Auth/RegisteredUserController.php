<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\UseCases\Auth\RegisterUserUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(Request $request): Response
    {
        // The landing page's call to action hands the email over in the query string.
        $email = $request->query('email');

        return Inertia::render('auth/Register', [
            'email' => is_string($email) ? $email : '',
        ]);
    }

    public function store(RegisterRequest $request, RegisterUserUseCase $register): RedirectResponse
    {
        $register->execute(
            $request->validated('name'),
            $request->validated('email'),
            $request->validated('password'),
        );

        return to_route('dashboard');
    }
}
