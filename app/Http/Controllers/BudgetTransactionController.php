<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BudgetTransactionRequest;
use App\Http\Requests\MoveBudgetTransactionRequest;
use App\Models\BudgetCategory;
use App\Models\BudgetTransaction;
use App\Traits\ResolvesHouseholdTrait;
use App\UseCases\Budget\CreateBudgetTransactionUseCase;
use App\UseCases\Budget\MoveBudgetTransactionUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BudgetTransactionController extends Controller
{
    use ResolvesHouseholdTrait;

    public function store(BudgetTransactionRequest $request, CreateBudgetTransactionUseCase $create): RedirectResponse
    {
        // Household ownership of the category is already enforced by the
        // request's household-scoped `exists` rule on budget_category_id.
        $category = BudgetCategory::findOrFail($request->validated('budget_category_id'));

        $transaction = $create->execute($category, $request->transactionAttributes());

        return back()->with('toast', 'Logged '.$transaction->label);
    }

    public function updateCategory(MoveBudgetTransactionRequest $request, BudgetTransaction $transaction, MoveBudgetTransactionUseCase $move): RedirectResponse
    {
        $this->assertOwned($request, $transaction);

        // Household ownership of the new category is already enforced by the
        // request's household-scoped `exists` rule on budget_category_id.
        $newCategory = BudgetCategory::findOrFail($request->validated('budget_category_id'));

        $moved = $move->execute($transaction, $newCategory);

        return back()->with('toast', "{$moved->label} → {$moved->categoryLabel}");
    }

    private function assertOwned(Request $request, BudgetTransaction $transaction): void
    {
        abort_if($transaction->household_id !== $this->household($request)->id, 404);
    }
}
