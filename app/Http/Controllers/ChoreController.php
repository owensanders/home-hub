<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ChoreStatus;
use App\Http\Requests\MoveChoreRequest;
use App\Models\Chore;
use App\Traits\ResolvesHouseholdTrait;
use App\UseCases\Chores\GetChoreBoard;
use App\UseCases\Chores\MoveChore;
use App\UseCases\Chores\ToggleChore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChoreController extends Controller
{
    use ResolvesHouseholdTrait;

    public function index(Request $request, GetChoreBoard $getChoreBoard): Response
    {
        return Inertia::render('Chores', $getChoreBoard->execute($this->household($request)));
    }

    public function toggle(Request $request, Chore $chore, ToggleChore $toggle): RedirectResponse
    {
        $this->assertOwned($request, $chore);

        $updated = $toggle->execute($chore);

        return back()->with('toast', $updated->done ? "{$updated->name} — nice one" : null);
    }

    public function move(MoveChoreRequest $request, Chore $chore, MoveChore $move): RedirectResponse
    {
        $this->assertOwned($request, $chore);

        $status = ChoreStatus::from($request->validated('status'));
        $move->execute($chore, $status);

        return back()->with('toast', "Moved to {$status->label()}");
    }

    private function assertOwned(Request $request, Chore $chore): void
    {
        abort_if($chore->household_id !== $this->household($request)->id, 404);
    }
}
