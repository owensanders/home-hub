<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\IndexMealPlannerRequest;
use App\Http\Requests\RescheduleMealRequest;
use App\Models\PlannedMeal;
use App\Traits\ResolvesHouseholdTrait;
use App\UseCases\Meals\GetMealPlannerUseCase;
use App\UseCases\Meals\RescheduleMealUseCase;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MealPlannerController extends Controller
{
    use ResolvesHouseholdTrait;

    public function index(IndexMealPlannerRequest $request, GetMealPlannerUseCase $getMealPlanner): Response
    {
        $week = $request->validated('week');

        return Inertia::render('MealPlanner', $getMealPlanner->execute(
            $this->household($request),
            $week !== null ? CarbonImmutable::createFromFormat('!Y-m-d', $week) : CarbonImmutable::now(),
        ));
    }

    public function reschedule(
        RescheduleMealRequest $request,
        PlannedMeal $meal,
        RescheduleMealUseCase $rescheduleMeal,
    ): RedirectResponse {
        abort_if($meal->household_id !== $this->household($request)->id, 404);

        $moved = $rescheduleMeal->execute($meal, CarbonImmutable::parse($request->validated('planned_on')));

        return back()->with('toast', "{$moved->name} moved");
    }
}
