<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\ShoppingRepositoryInterface;
use App\Http\Requests\StoreShoppingItemRequest;
use App\Models\ShoppingItem;
use App\Traits\ResolvesHouseholdTrait;
use App\UseCases\Shopping\AddShoppingItem;
use App\UseCases\Shopping\GetShoppingScreen;
use App\UseCases\Shopping\ToggleShoppingItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShoppingListController extends Controller
{
    use ResolvesHouseholdTrait;

    public function __construct(private readonly ShoppingRepositoryInterface $shopping) {}

    public function index(Request $request, GetShoppingScreen $getShoppingScreen, ?string $slug = null): Response
    {
        $household = $this->household($request);

        $active = $slug !== null
            ? $this->shopping->findListBySlug($household, $slug)
            : null;

        abort_if($slug !== null && $active === null, 404);

        return Inertia::render('ShoppingLists', $getShoppingScreen->execute($household, $active));
    }

    public function store(StoreShoppingItemRequest $request, string $slug, AddShoppingItem $addItem): RedirectResponse
    {
        $list = $this->shopping->findListBySlug($this->household($request), $slug);

        abort_if($list === null, 404);

        $item = $addItem->execute($list, $request->validated('name'), $request->validated('quantity'));

        return back()->with('toast', "“{$item->name}” added to {$list->name}");
    }

    public function toggle(Request $request, ShoppingItem $item, ToggleShoppingItem $toggle): RedirectResponse
    {
        abort_if($item->list->household_id !== $this->household($request)->id, 404);

        $toggle->execute($item);

        return back();
    }
}
