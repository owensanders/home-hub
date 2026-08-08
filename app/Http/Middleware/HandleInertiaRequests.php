<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\Repositories\ChoreRepositoryInterface;
use App\Contracts\Repositories\ShoppingRepositoryInterface;
use App\Enums\ChoreStatus;
use App\Models\Household;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(
        private readonly ShoppingRepositoryInterface $shopping,
        private readonly ChoreRepositoryInterface $chores,
    ) {}

    /** @see https://inertiajs.com/asset-versioning */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $household = $request->user()?->household;

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'household' => $household !== null
                ? ['id' => $household->id, 'name' => $household->name]
                : null,
            'navCounts' => $household !== null ? $this->navCounts($household) : null,
            'flash' => [
                'toast' => fn () => $request->session()->get('toast'),
            ],
        ];
    }

    /**
     * The two badges the sidebar shows: items still to buy on the default list,
     * and chores still due today.
     *
     * @return array{shopping: int, chores: int}
     */
    private function navCounts(Household $household): array
    {
        return [
            'shopping' => $this->shopping->countRemainingOnDefaultList($household),
            'chores' => $this->chores->countByStatus($household, ChoreStatus::Today),
        ];
    }
}
