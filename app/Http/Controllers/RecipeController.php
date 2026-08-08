<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RecipeRequest;
use App\Models\Recipe;
use App\Traits\ResolvesHouseholdTrait;
use App\UseCases\Meals\AddRecipeUseCase;
use App\UseCases\Meals\DeleteRecipeUseCase;
use App\UseCases\Meals\GetRecipeLibraryUseCase;
use App\UseCases\Meals\UpdateRecipeUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecipeController extends Controller
{
    use ResolvesHouseholdTrait;

    public function index(Request $request, GetRecipeLibraryUseCase $getLibrary): Response
    {
        return Inertia::render('Recipes', [
            'recipes' => $getLibrary->execute($this->household($request)),
        ]);
    }

    public function store(RecipeRequest $request, AddRecipeUseCase $add): RedirectResponse
    {
        $recipe = $add->execute($this->household($request), $request->recipeAttributes());

        return back()->with('toast', "{$recipe->name} added");
    }

    public function update(RecipeRequest $request, Recipe $recipe, UpdateRecipeUseCase $update): RedirectResponse
    {
        $this->assertOwned($request, $recipe);

        $updated = $update->execute($recipe, $request->recipeAttributes());

        return back()->with('toast', "{$updated->name} updated");
    }

    public function destroy(Request $request, Recipe $recipe, DeleteRecipeUseCase $delete): RedirectResponse
    {
        $this->assertOwned($request, $recipe);

        $name = $recipe->name;
        $delete->execute($recipe);

        return back()->with('toast', "{$name} deleted");
    }

    private function assertOwned(Request $request, Recipe $recipe): void
    {
        abort_if($recipe->household_id !== $this->household($request)->id, 404);
    }
}
