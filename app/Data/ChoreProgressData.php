<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class ChoreProgressData extends Data
{
    public function __construct(
        public int $done,
        public int $total,
        public int $percentage,
    ) {}

    public static function make(int $done, int $total): self
    {
        return new self($done, $total, $total > 0 ? (int) round($done / $total * 100) : 0);
    }
}
