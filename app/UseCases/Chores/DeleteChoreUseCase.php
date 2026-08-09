<?php

declare(strict_types=1);

namespace App\UseCases\Chores;

use App\Contracts\Repositories\ChoreRepositoryInterface;
use App\Models\Chore;

class DeleteChoreUseCase
{
    public function __construct(private readonly ChoreRepositoryInterface $chores) {}

    public function execute(Chore $chore): void
    {
        $this->chores->delete($chore);
    }
}
