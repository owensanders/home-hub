<?php

declare(strict_types=1);

namespace App\UseCases\Chores;

use App\Contracts\Repositories\ChoreRepositoryInterface;
use App\Data\ChoreData;
use App\Enums\ChoreStatus;
use App\Models\Chore;

class MoveChore
{
    public function __construct(private readonly ChoreRepositoryInterface $chores) {}

    public function execute(Chore $chore, ChoreStatus $status): ChoreData
    {
        return ChoreData::fromModel($this->chores->moveTo($chore, $status));
    }
}
