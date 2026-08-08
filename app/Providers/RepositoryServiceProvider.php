<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\CalendarEventRepositoryInterface;
use App\Contracts\Repositories\ChoreRepositoryInterface;
use App\Contracts\Repositories\HouseholdRepositoryInterface;
use App\Contracts\Repositories\MealPlanRepositoryInterface;
use App\Contracts\Repositories\ShoppingRepositoryInterface;
use App\Repositories\CalendarEventRepository;
use App\Repositories\ChoreRepository;
use App\Repositories\HouseholdRepository;
use App\Repositories\MealPlanRepository;
use App\Repositories\ShoppingRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        CalendarEventRepositoryInterface::class => CalendarEventRepository::class,
        ChoreRepositoryInterface::class => ChoreRepository::class,
        HouseholdRepositoryInterface::class => HouseholdRepository::class,
        MealPlanRepositoryInterface::class => MealPlanRepository::class,
        ShoppingRepositoryInterface::class => ShoppingRepository::class,
    ];
}
