<?php

declare(strict_types=1);

namespace App\UseCases\Chores;

use App\Contracts\Repositories\ChoreRepositoryInterface;
use App\Data\ChoreData;
use App\Models\Chore;

class UpdateChoreUseCase
{
    public function __construct(private readonly ChoreRepositoryInterface $chores) {}

    /** @param array<string, mixed> $attributes */
    public function execute(Chore $chore, array $attributes): ChoreData
    {
        return ChoreData::fromModel($this->chores->update($chore, $attributes));
    }
}
