<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\User;
use Spatie\LaravelData\Data;

class HouseMemberData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $initials,
        public string $colour,
        public string $email,
        public string $role,
        public string $roleLabel,
        public string $activity,
        public bool $you,
        public bool $pending,
        public ?string $pendingReason,
    ) {}

    public static function fromModel(User $user, int $viewerId): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            initials: $user->initials ?? mb_strtoupper(mb_substr($user->name, 0, 2)),
            colour: $user->colour->cssVar(),
            email: $user->email,
            role: $user->pivot->role->value,
            roleLabel: $user->pivot->role->label(),
            activity: $user->status_line ?? '',
            you: $user->id === $viewerId,
            pending: $user->pivot->pending,
            pendingReason: $user->pivot->pending_reason?->value,
        );
    }
}
