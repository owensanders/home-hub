<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ChoreStatus;
use App\Models\Chore;
use Spatie\LaravelData\Data;

class ChoreData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $status,
        public bool $done,
        public ?string $dueLabel,
        public ?string $repeatLabel,
        public ?MemberData $assignee,
    ) {}

    public static function fromModel(Chore $chore): self
    {
        return new self(
            id: $chore->id,
            name: $chore->name,
            status: $chore->status->value,
            done: $chore->status === ChoreStatus::Done,
            dueLabel: $chore->due_label,
            repeatLabel: $chore->repeat_label,
            assignee: $chore->assignee !== null ? MemberData::fromModel($chore->assignee) : null,
        );
    }
}
