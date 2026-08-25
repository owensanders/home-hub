<?php

declare(strict_types=1);

namespace App\UseCases\Chores;

use App\Contracts\Repositories\ChoreRepositoryInterface;
use App\Data\ChoreData;
use App\Enums\ChoreStatus;
use App\Models\Chore;
use Illuminate\Support\Carbon;

class UpdateChoreUseCase
{
    public function __construct(private readonly ChoreRepositoryInterface $chores) {}

    /** @param array<string, mixed> $attributes */
    public function execute(Chore $chore, array $attributes): ChoreData
    {
        $dueDate = Carbon::parse($attributes['due_date']);
        $dueDateChanged = ! $dueDate->isSameDay($chore->due_date);

        // Only re-derive the column from the date when the date itself moved — otherwise
        // an unrelated edit (e.g. reassigning) would silently undo a manual drag-and-drop.
        if ($chore->status !== ChoreStatus::Done && $dueDateChanged) {
            $attributes['status'] = ChoreStatus::fromDueDate($dueDate)->value;
        }

        return ChoreData::fromModel($this->chores->update($chore, $attributes));
    }
}
