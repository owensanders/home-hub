<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Traits\ResolvesHouseholdTrait;
use App\UseCases\Dashboard\GetDashboard;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use ResolvesHouseholdTrait;

    public function __invoke(Request $request, GetDashboard $getDashboard): Response
    {
        return Inertia::render('Dashboard', [
            'dashboard' => $getDashboard->execute(
                $this->household($request),
                str($request->user()->name)->before(' ')->toString(),
                CarbonImmutable::now(),
            ),
        ]);
    }
}
