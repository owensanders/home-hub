<?php

declare(strict_types=1);

namespace App\UseCases\Chores;

use App\Contracts\Repositories\ChoreRepositoryInterface;
use App\Data\ChoreData;
use App\Enums\ChoreStatus;
use App\Models\Chore;

/** Ticking a chore off moves it to Completed; un-ticking sends it back to Today. */
class ToggleChoreUseCase
{
    public function __construct(private readonly ChoreRepositoryInterface $chores) {}

    public function execute(Chore $chore): ChoreData
    {
        $target = $chore->status === ChoreStatus::Done
            ? ChoreStatus::Today
            : ChoreStatus::Done;

        return ChoreData::fromModel($this->chores->moveTo($chore, $target));
    }
}
