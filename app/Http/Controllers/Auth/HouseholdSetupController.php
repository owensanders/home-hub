<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Data\PlanData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\HouseholdJoinRequest;
use App\Http\Requests\Auth\HouseholdSetupRequest;
use App\UseCases\House\ChangeHouseholdPlanUseCase;
use App\UseCases\House\CreateHouseholdUseCase;
use App\UseCases\House\JoinHouseholdUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HouseholdSetupController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->user()->household_id !== null) {
            return $this->redirectAlreadyHoused($request);
        }

        return Inertia::render('auth/HouseholdSetup', ['plans' => PlanData::catalog()]);
    }

    public function store(
        HouseholdSetupRequest $request,
        CreateHouseholdUseCase $createHousehold,
        ChangeHouseholdPlanUseCase $changePlan,
    ): RedirectResponse|SymfonyResponse {
        if ($request->user()->household_id !== null) {
            return $this->redirectAlreadyHoused($request);
        }

        $attributes = $request->householdAttributes();
        $household = $createHousehold->execute($request->user(), $attributes);

        if (! blank($attributes['plan']) && $attributes['plan'] !== 'free') {
            try {
                $checkoutUrl = $changePlan->execute(
                    $household,
                    $attributes['plan'],
                    (string) $attributes['cycle'],
                    successUrl: route('household.done'),
                    cancelUrl: route('household.done'),
                );

                if ($checkoutUrl !== null) {
                    return Inertia::location($checkoutUrl);
                }
            } catch (RuntimeException) {
                // The chosen plan isn't purchasable yet (e.g. Stripe price not configured) —
                // the household still exists, so fall through and land it on Free rather than
                // losing the signup.
            }
        }

        return to_route('household.done')->with('householdSetup', [
            'mode' => 'create',
            'householdName' => $household->name,
            'inviteCount' => count($request->validated('invites', [])),
        ]);
    }

    public function join(HouseholdJoinRequest $request, JoinHouseholdUseCase $joinHousehold): RedirectResponse
    {
        if ($request->user()->household_id !== null) {
            return $this->redirectAlreadyHoused($request);
        }

        $household = $joinHousehold->execute($request->user(), (string) $request->validated('code'));

        return to_route('household.done')->with('householdSetup', [
            'mode' => 'join',
            'householdName' => $household->name,
            'inviteCount' => 0,
        ]);
    }

    public function done(Request $request): Response
    {
        /** @var array{mode: string, householdName: string, inviteCount: int}|null $setup */
        $setup = $request->session()->get('householdSetup');
        $user = $request->user();
        $household = $user?->household;

        return Inertia::render('auth/HouseholdDone', [
            'mode' => $setup['mode'] ?? ($user?->pending ? 'join' : 'create'),
            'householdName' => $setup['householdName'] ?? $household->name ?? '',
            'inviteCount' => $setup['inviteCount'] ?? 0,
            'pending' => $user !== null && $user->pending,
        ]);
    }

    private function redirectAlreadyHoused(Request $request): RedirectResponse
    {
        return $request->user()->pending ? to_route('household.done') : to_route('dashboard');
    }
}
