<?php

declare(strict_types=1);

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ChoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncomeSourceController;
use App\Http\Controllers\MealPlannerController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ShoppingListController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome'))->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('meals', [MealPlannerController::class, 'index'])->name('meals.index');
    Route::post('meals', [MealPlannerController::class, 'store'])->name('meals.store');
    Route::patch('meals/{meal}', [MealPlannerController::class, 'update'])->name('meals.update');
    Route::delete('meals/{meal}', [MealPlannerController::class, 'destroy'])->name('meals.destroy');
    Route::patch('meals/{meal}/reschedule', [MealPlannerController::class, 'reschedule'])->name('meals.reschedule');

    Route::get('recipes', [RecipeController::class, 'index'])->name('recipes.index');
    Route::post('recipes', [RecipeController::class, 'store'])->name('recipes.store');
    Route::patch('recipes/{recipe}', [RecipeController::class, 'update'])->name('recipes.update');
    Route::delete('recipes/{recipe}', [RecipeController::class, 'destroy'])->name('recipes.destroy');

    Route::get('shopping/{slug?}', [ShoppingListController::class, 'index'])->name('shopping.index');
    Route::post('shopping/{slug}/items', [ShoppingListController::class, 'store'])->name('shopping.items.store');
    Route::patch('shopping-items/{item}/toggle', [ShoppingListController::class, 'toggle'])->name('shopping.items.toggle');
    Route::patch('shopping-items/{item}', [ShoppingListController::class, 'updateItem'])->name('shopping.items.update');
    Route::delete('shopping-items/{item}', [ShoppingListController::class, 'destroyItem'])->name('shopping.items.destroy');

    Route::post('shopping-lists', [ShoppingListController::class, 'storeList'])->name('shopping.lists.store');
    Route::patch('shopping-lists/{list}', [ShoppingListController::class, 'updateList'])->name('shopping.lists.update');
    Route::delete('shopping-lists/{list}', [ShoppingListController::class, 'destroyList'])->name('shopping.lists.destroy');

    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('calendar/events', [CalendarController::class, 'store'])->name('calendar.events.store');
    Route::patch('calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.events.update');
    Route::delete('calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.events.destroy');

    Route::get('chores', [ChoreController::class, 'index'])->name('chores.index');
    Route::post('chores', [ChoreController::class, 'store'])->name('chores.store');
    Route::patch('chores/{chore}', [ChoreController::class, 'update'])->name('chores.update');
    Route::delete('chores/{chore}', [ChoreController::class, 'destroy'])->name('chores.destroy');
    Route::patch('chores/{chore}/toggle', [ChoreController::class, 'toggle'])->name('chores.toggle');
    Route::patch('chores/{chore}/move', [ChoreController::class, 'move'])->name('chores.move');

    Route::get('budget', [BudgetController::class, 'index'])->name('budget.index');
    Route::post('budget/categories', [BudgetController::class, 'storeCategory'])->name('budget.categories.store');
    Route::patch('budget/categories/{category}', [BudgetController::class, 'updateCategory'])->name('budget.categories.update');
    Route::delete('budget/categories/{category}', [BudgetController::class, 'destroyCategory'])->name('budget.categories.destroy');

    Route::post('budget/income', [IncomeSourceController::class, 'store'])->name('budget.income.store');
    Route::patch('budget/income/{income}', [IncomeSourceController::class, 'update'])->name('budget.income.update');
    Route::delete('budget/income/{income}', [IncomeSourceController::class, 'destroy'])->name('budget.income.destroy');

    // Screens the design leaves for a later pass. They render the "not designed
    // yet" empty state rather than 404ing, so the sidebar stays navigable.
    foreach (['house', 'documents'] as $screen) {
        Route::get($screen, fn () => Inertia::render('Placeholder', ['screen' => $screen]))->name("{$screen}.index");
    }
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
