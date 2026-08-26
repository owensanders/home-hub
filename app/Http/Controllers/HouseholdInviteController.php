<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Household;
use App\UseCases\House\AcceptHouseholdInviteUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HouseholdInviteController extends Controller
{
    public function accept(Request $request, AcceptHouseholdInviteUseCase $accept): RedirectResponse
    {
        abort_if($request->user()->id !== (int) $request->query('member'), 403);

        $household = Household::findOrFail((int) $request->query('household'));

        $accept->execute($request->user(), $household);

        return to_route('household.select')->with('toast', "You're in {$household->name} — pick a household to view");
    }
}
