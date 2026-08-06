<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

/**
 * A member's weekly chore tally, shown as the dial on the chores board.
 */
class MemberScoreData extends Data
{
    public function __construct(
        public MemberData $member,
        public int $done,
        public int $total,
        public int $percentage,
    ) {}

    public static function make(MemberData $member, int $done, int $total): self
    {
        return new self($member, $done, $total, $total > 0 ? (int) round($done / $total * 100) : 0);
    }
}
