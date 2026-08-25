<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ChoreStatus;
use App\Models\Chore;
use Illuminate\Support\Carbon;
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
        public string $dueDate,
        public string $repeat,
        public ?MemberData $assignee,
    ) {}

    public static function fromModel(Chore $chore): self
    {
        return new self(
            id: $chore->id,
            name: $chore->name,
            status: $chore->status->value,
            done: $chore->status === ChoreStatus::Done,
            dueLabel: self::formatDueLabel($chore->due_date),
            repeatLabel: $chore->repeat->chip(),
            dueDate: $chore->due_date->toDateString(),
            repeat: $chore->repeat->value,
            assignee: $chore->assignee !== null ? MemberData::fromModel($chore->assignee) : null,
        );
    }

    private static function formatDueLabel(Carbon $date): string
    {
        $today = Carbon::today();
        $date = $date->copy()->startOfDay();

        return match (true) {
            $date->lte($today) => 'Due today',
            $date->equalTo($today->copy()->addDay()) => 'Tomorrow',
            default => $date->format('D, j M'),
        };
    }
}
