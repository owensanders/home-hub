<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Enums\ChoreStatus;
use App\Models\Chore;
use App\Models\Household;
use Illuminate\Support\Collection;

interface ChoreRepositoryInterface
{
    /** @return Collection<int, Chore> */
    public function allFor(Household $household): Collection;

    public function countByStatus(Household $household, ChoreStatus $status): int;

    public function moveTo(Chore $chore, ChoreStatus $status): Chore;
}
