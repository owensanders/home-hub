<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Household;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->isLocal()) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        Password::defaults(fn () => Password::min(10));

        Cashier::useCustomerModel(Household::class);
    }
}
