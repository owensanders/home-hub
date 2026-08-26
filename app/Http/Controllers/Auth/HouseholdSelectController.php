<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\HouseholdSelectRequest;
use App\UseCases\House\GetHouseholdOptionsUseCase;
use App\UseCases\House\SwitchCurrentHouseholdUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HouseholdSelectController extends Controller
{
    public function show(Request $request, GetHouseholdOptionsUseCase $getOptions): Response
    {
        return Inertia::render('auth/HouseholdSelect', [
            'households' => $getOptions->execute($request->user()),
            'currentHouseholdId' => $request->user()->current_household_id,
        ]);
    }

    public function store(HouseholdSelectRequest $request, SwitchCurrentHouseholdUseCase $switch): RedirectResponse
    {
        $switch->execute($request->user(), (int) $request->validated('household_id'));

        return to_route('dashboard');
    }
}
