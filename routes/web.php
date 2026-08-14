<?php

declare(strict_types=1);

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ChoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\IncomeSourceController;
use App\Http\Controllers\MealPlannerController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ShoppingListController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome'))->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // View-only screens every household role can see.
    Route::get('meals', [MealPlannerController::class, 'index'])->name('meals.index');
    Route::get('recipes', [RecipeController::class, 'index'])->name('recipes.index');
    Route::get('shopping/{slug?}', [ShoppingListController::class, 'index'])->name('shopping.index');
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('chores', [ChoreController::class, 'index'])->name('chores.index');

    // Ticking off is allowed beyond Owner/Adult. Chores' "only their own
    // chore" rule depends on which chore, so it's enforced in the
    // controller rather than the route; shopping items aren't assigned to
    // anyone, so Teen (not Child) is a plain role check here.
    Route::patch('chores/{chore}/toggle', [ChoreController::class, 'toggle'])->name('chores.toggle');
    Route::patch('shopping-items/{item}/toggle', [ShoppingListController::class, 'toggle'])
        ->name('shopping.items.toggle')
        ->middleware('role:owner,adult,teen');

    // Calendar events are shared, not owned by anyone, and handy for a Teen
    // to manage themselves (e.g. school schedules) — so Teen gets full CRUD
    // here too, unlike the other Owner/Adult-only mutations below.
    Route::middleware('role:owner,adult,teen')->group(function (): void {
        Route::post('calendar/events', [CalendarController::class, 'store'])->name('calendar.events.store');
        Route::patch('calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.events.update');
        Route::delete('calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.events.destroy');
    });

    // Everything else that creates, edits, or deletes household data —
    // Owner/Adult only. Teen and Child get 403s.
    Route::middleware('role:owner,adult')->group(function (): void {
        Route::post('meals', [MealPlannerController::class, 'store'])->name('meals.store');
        Route::patch('meals/{meal}', [MealPlannerController::class, 'update'])->name('meals.update');
        Route::delete('meals/{meal}', [MealPlannerController::class, 'destroy'])->name('meals.destroy');
        Route::patch('meals/{meal}/reschedule', [MealPlannerController::class, 'reschedule'])->name('meals.reschedule');

        Route::post('recipes', [RecipeController::class, 'store'])->name('recipes.store');
        Route::patch('recipes/{recipe}', [RecipeController::class, 'update'])->name('recipes.update');
        Route::delete('recipes/{recipe}', [RecipeController::class, 'destroy'])->name('recipes.destroy');

        Route::post('shopping/{slug}/items', [ShoppingListController::class, 'store'])->name('shopping.items.store');
        Route::patch('shopping-items/{item}', [ShoppingListController::class, 'updateItem'])->name('shopping.items.update');
        Route::delete('shopping-items/{item}', [ShoppingListController::class, 'destroyItem'])->name('shopping.items.destroy');

        Route::post('shopping-lists', [ShoppingListController::class, 'storeList'])->name('shopping.lists.store');
        Route::patch('shopping-lists/{list}', [ShoppingListController::class, 'updateList'])->name('shopping.lists.update');
        Route::delete('shopping-lists/{list}', [ShoppingListController::class, 'destroyList'])->name('shopping.lists.destroy');

        Route::post('chores', [ChoreController::class, 'store'])->name('chores.store');
        Route::patch('chores/{chore}', [ChoreController::class, 'update'])->name('chores.update');
        Route::delete('chores/{chore}', [ChoreController::class, 'destroy'])->name('chores.destroy');
        Route::patch('chores/{chore}/move', [ChoreController::class, 'move'])->name('chores.move');

        Route::get('budget', [BudgetController::class, 'index'])->name('budget.index');
        Route::post('budget/categories', [BudgetController::class, 'storeCategory'])->name('budget.categories.store');
        Route::patch('budget/categories/{category}', [BudgetController::class, 'updateCategory'])->name('budget.categories.update');
        Route::delete('budget/categories/{category}', [BudgetController::class, 'destroyCategory'])->name('budget.categories.destroy');

        Route::post('budget/income', [IncomeSourceController::class, 'store'])->name('budget.income.store');
        Route::patch('budget/income/{income}', [IncomeSourceController::class, 'update'])->name('budget.income.update');
        Route::delete('budget/income/{income}', [IncomeSourceController::class, 'destroy'])->name('budget.income.destroy');

        Route::get('house', [HouseController::class, 'index'])->name('house.index');
        Route::post('house/invite', [HouseController::class, 'invite'])->name('house.invite');
        Route::patch('house/members/{member}/role', [HouseController::class, 'updateRole'])->name('house.members.role');
        Route::delete('house/members/{member}', [HouseController::class, 'destroy'])->name('house.members.destroy');
        Route::patch('house/settings', [HouseController::class, 'toggleSetting'])->name('house.settings.update');

        Route::get('documents/{folder?}', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('document-folders', [DocumentController::class, 'storeFolder'])->name('documents.folders.store');
        Route::delete('document-folders/{folder}', [DocumentController::class, 'destroyFolder'])->name('documents.folders.destroy');
        Route::post('document-folders/{folder}/documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::patch('documents/{document}/move', [DocumentController::class, 'move'])->name('documents.move');
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
