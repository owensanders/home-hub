<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RescheduleMealRequest;
use App\Models\PlannedMeal;
use App\Traits\ResolvesHouseholdTrait;
use App\UseCases\Meals\GetMealPlanner;
use App\UseCases\Meals\RescheduleMeal;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MealPlannerController extends Controller
{
    use ResolvesHouseholdTrait;

    public function index(Request $request, GetMealPlanner $getMealPlanner): Response
    {
        $week = $request->date('week') !== null
            ? CarbonImmutable::parse($request->date('week'))
            : CarbonImmutable::now();

        return Inertia::render('MealPlanner', $getMealPlanner->execute($this->household($request), $week));
    }

    public function reschedule(
        RescheduleMealRequest $request,
        PlannedMeal $meal,
        RescheduleMeal $rescheduleMeal,
    ): RedirectResponse {
        abort_if($meal->household_id !== $this->household($request)->id, 404);

        $moved = $rescheduleMeal->execute($meal, CarbonImmutable::parse($request->validated('planned_on')));

        return back()->with('toast', "{$moved->name} moved");
    }
}
