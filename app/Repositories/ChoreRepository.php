<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\ChoreRepositoryInterface;
use App\Enums\ChoreStatus;
use App\Models\Chore;
use App\Models\Household;
use Illuminate\Support\Collection;

class ChoreRepository implements ChoreRepositoryInterface
{
    /** @return Collection<int, Chore> */
    public function allFor(Household $household): Collection
    {
        return $household->chores()
            ->with('assignee')
            ->orderBy('position')
            ->orderBy('id')
            ->get();
    }

    public function countByStatus(Household $household, ChoreStatus $status): int
    {
        return $household->chores()->where('status', $status)->count();
    }

    public function moveTo(Chore $chore, ChoreStatus $status): Chore
    {
        $chore->update(['status' => $status]);

        return $chore->refresh();
    }
}
