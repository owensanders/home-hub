<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Household;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait ResolvesHouseholdTrait
{
    /**
     * Every screen is scoped to the signed-in user's household. A user without
     * one has nothing to show, so treat it as a hard stop rather than
     * rendering a half-empty hub.
     *
     * @throws HttpException
     */
    protected function household(Request $request): Household
    {
        $household = $request->user()?->household;

        abort_if($household === null, 403, 'You are not a member of a household yet.');

        return $household;
    }
}
