<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\HouseholdSelectRequest;
use App\Http\Requests\Settings\LeaveHouseholdRequest;
use App\Models\Household;
use App\UseCases\House\GetHouseholdOptionsUseCase;
use App\UseCases\House\LeaveHouseholdUseCase;
use App\UseCases\House\SwitchCurrentHouseholdUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HouseholdController extends Controller
{
    public function edit(Request $request, GetHouseholdOptionsUseCase $getOptions): Response
    {
        return Inertia::render('settings/Households', [
            'households' => $getOptions->execute($request->user()),
            'currentHouseholdId' => $request->user()->current_household_id,
        ]);
    }

    public function switchCurrent(HouseholdSelectRequest $request, SwitchCurrentHouseholdUseCase $switch): RedirectResponse
    {
        $switch->execute($request->user(), (int) $request->validated('household_id'));

        return to_route('dashboard');
    }

    public function leave(LeaveHouseholdRequest $request, LeaveHouseholdUseCase $leave): RedirectResponse
    {
        $household = Household::findOrFail($request->validated('household_id'));
        $householdName = $household->name;

        $leave->execute($request->user(), $household, $request->boolean('confirm_household_deletion'));

        if ($request->user()->households()->doesntExist()) {
            return to_route('household.setup');
        }

        return back()->with('toast', "You left {$householdName}");
    }
}
