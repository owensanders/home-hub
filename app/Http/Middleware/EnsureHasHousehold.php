<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasHousehold
{
    /**
     * A verified user with no household yet has nowhere else useful to go —
     * send them to finish the setup wizard instead of 403ing on every page.
     * A user who joined by code but isn't approved yet doesn't get in either;
     * the owner has to action their pending membership first.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->household_id === null) {
            return redirect()->route('household.setup');
        }

        if ($user->pending) {
            return redirect()->route('household.done');
        }

        return $next($request);
    }
}
