<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class HouseSettingData extends Data
{
    public function __construct(
        public string $key,
        public string $title,
        public string $body,
        public bool $enabled,
    ) {}
}
