<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\HouseholdRepositoryInterface;
use App\Data\MemberData;
use App\Enums\ChoreStatus;
use App\Http\Requests\ChoreRequest;
use App\Http\Requests\MoveChoreRequest;
use App\Models\Chore;
use App\Traits\ResolvesHouseholdTrait;
use App\UseCases\Chores\CreateChoreUseCase;
use App\UseCases\Chores\DeleteChoreUseCase;
use App\UseCases\Chores\GetChoreBoardUseCase;
use App\UseCases\Chores\MoveChoreUseCase;
use App\UseCases\Chores\ToggleChoreUseCase;
use App\UseCases\Chores\UpdateChoreUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChoreController extends Controller
{
    use ResolvesHouseholdTrait;

    public function __construct(private readonly HouseholdRepositoryInterface $households) {}

    public function index(Request $request, GetChoreBoardUseCase $getChoreBoard): Response
    {
        $household = $this->household($request);

        return Inertia::render('Chores', [
            ...$getChoreBoard->execute($household),
            'members' => $this->households->members($household)->map(MemberData::fromModel(...))->values(),
        ]);
    }

    public function store(ChoreRequest $request, CreateChoreUseCase $create): RedirectResponse
    {
        $chore = $create->execute($this->household($request), $request->choreAttributes());

        return back()->with('toast', "{$chore->name} added");
    }

    public function update(ChoreRequest $request, Chore $chore, UpdateChoreUseCase $update): RedirectResponse
    {
        $this->assertOwned($request, $chore);

        $updated = $update->execute($chore, $request->choreAttributes());

        return back()->with('toast', "{$updated->name} updated");
    }

    public function destroy(Request $request, Chore $chore, DeleteChoreUseCase $delete): RedirectResponse
    {
        $this->assertOwned($request, $chore);

        $name = $chore->name;
        $delete->execute($chore);

        return back()->with('toast', "{$name} deleted");
    }

    public function toggle(Request $request, Chore $chore, ToggleChoreUseCase $toggle): RedirectResponse
    {
        $this->assertOwned($request, $chore);

        $updated = $toggle->execute($chore);

        return back()->with('toast', $updated->done ? "{$updated->name} — nice one" : null);
    }

    public function move(MoveChoreRequest $request, Chore $chore, MoveChoreUseCase $move): RedirectResponse
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
