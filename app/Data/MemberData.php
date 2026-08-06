<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\User;
use Spatie\LaravelData\Data;

class MemberData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $initials,
        public string $colour,
        public ?string $status,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            initials: $user->initials ?? mb_strtoupper(mb_substr($user->name, 0, 2)),
            colour: $user->colour->cssVar(),
            status: $user->status_line,
        );
    }
}
