<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HouseholdRole;
use App\Http\Requests\InviteRequest;
use App\Http\Requests\RoleRequest;
use App\Http\Requests\SettingRequest;
use App\Models\User;
use App\Traits\ResolvesHouseholdTrait;
use App\UseCases\House\ChangeMemberRoleUseCase;
use App\UseCases\House\GetHouseUseCase;
use App\UseCases\House\InviteMemberUseCase;
use App\UseCases\House\RemoveMemberUseCase;
use App\UseCases\House\ToggleHouseholdSettingUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HouseController extends Controller
{
    use ResolvesHouseholdTrait;

    public function index(Request $request, GetHouseUseCase $getHouse): Response
    {
        return Inertia::render('House', [
            'house' => $getHouse->execute($this->household($request), $request->user()->id),
        ]);
    }

    public function invite(InviteRequest $request, InviteMemberUseCase $invite): RedirectResponse
    {
        $member = $invite->execute($this->household($request), $request->inviteAttributes());

        return back()->with('toast', "Invite sent to {$member->email}");
    }

    public function updateRole(RoleRequest $request, User $member, ChangeMemberRoleUseCase $change): RedirectResponse
    {
        $this->assertOwned($request, $member);

        $updated = $change->execute($member, HouseholdRole::from($request->validated('role')));

        return back()->with('toast', "{$updated->name} is now {$updated->role->label()}");
    }

    public function destroy(Request $request, User $member, RemoveMemberUseCase $remove): RedirectResponse
    {
        $this->assertOwned($request, $member);

        $name = $member->name;
        $wasPending = $member->pending;
        $remove->execute($member);

        return back()->with('toast', $wasPending ? "{$name} — invite cancelled" : "{$name} removed from the household");
    }

    public function toggleSetting(SettingRequest $request, ToggleHouseholdSettingUseCase $toggle): RedirectResponse
    {
        $toggle->execute($this->household($request), (string) $request->validated('key'));

        return back();
    }

    private function assertOwned(Request $request, User $member): void
    {
        abort_if($member->household_id !== $this->household($request)->id, 404);
    }
}
