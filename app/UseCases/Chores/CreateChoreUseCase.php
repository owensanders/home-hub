<?php

declare(strict_types=1);

namespace App\UseCases\Chores;

use App\Contracts\Repositories\ChoreRepositoryInterface;
use App\Data\ChoreData;
use App\Enums\ChoreStatus;
use App\Models\Household;
use Illuminate\Support\Carbon;

class CreateChoreUseCase
{
    public function __construct(private readonly ChoreRepositoryInterface $chores) {}

    /** @param array<string, mixed> $attributes */
    public function execute(Household $household, array $attributes): ChoreData
    {
        $attributes['status'] = ChoreStatus::fromDueDate(Carbon::parse($attributes['due_date']))->value;

        return ChoreData::fromModel($this->chores->create($household, $attributes));
    }
}
