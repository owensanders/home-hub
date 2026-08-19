<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RecipeTag;
use App\Http\Requests\IndexMealPlannerRequest;
use App\Http\Requests\PlannedMealRequest;
use App\Http\Requests\RescheduleMealRequest;
use App\Models\PlannedMeal;
use App\Traits\ResolvesHouseholdTrait;
use App\UseCases\Meals\AddPlannedMealUseCase;
use App\UseCases\Meals\DeletePlannedMealUseCase;
use App\UseCases\Meals\GetMealPlannerUseCase;
use App\UseCases\Meals\RescheduleMealUseCase;
use App\UseCases\Meals\UpdatePlannedMealUseCase;
use App\UseCases\Shopping\GetShoppingListOptionsUseCase;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MealPlannerController extends Controller
{
    use ResolvesHouseholdTrait;

    public function index(
        IndexMealPlannerRequest $request,
        GetMealPlannerUseCase $getMealPlanner,
        GetShoppingListOptionsUseCase $getShoppingLists,
    ): Response {
        $week = $request->validated('week');
        $household = $this->household($request);

        return Inertia::render('MealPlanner', [
            ...$getMealPlanner->execute(
                $household,
                $week !== null ? CarbonImmutable::createFromFormat('!Y-m-d', $week) : CarbonImmutable::now(),
            ),
            'shoppingLists' => $getShoppingLists->execute($household),
            'tagOptions' => RecipeTag::options(),
        ]);
    }

    public function store(PlannedMealRequest $request, AddPlannedMealUseCase $add): RedirectResponse
    {
        $meal = $add->execute($this->household($request), $request->plannedMealAttributes());

        return back()->with('toast', "{$meal->name} added");
    }

    public function update(PlannedMealRequest $request, PlannedMeal $meal, UpdatePlannedMealUseCase $update): RedirectResponse
    {
        $this->assertOwned($request, $meal);

        $updated = $update->execute($meal, $request->plannedMealAttributes());

        return back()->with('toast', "{$updated->name} updated");
    }

    public function destroy(Request $request, PlannedMeal $meal, DeletePlannedMealUseCase $delete): RedirectResponse
    {
        $this->assertOwned($request, $meal);

        $name = $meal->recipe->name;
        $delete->execute($meal);

        return back()->with('toast', "{$name} removed");
    }

    public function reschedule(
        RescheduleMealRequest $request,
        PlannedMeal $meal,
        RescheduleMealUseCase $rescheduleMeal,
    ): RedirectResponse {
        $this->assertOwned($request, $meal);

        $moved = $rescheduleMeal->execute($meal, CarbonImmutable::parse($request->validated('planned_on')));

        return back()->with('toast', "{$moved->name} moved");
    }

    private function assertOwned(Request $request, PlannedMeal $meal): void
    {
        abort_if($meal->household_id !== $this->household($request)->id, 404);
    }
}
