<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\DocumentFolder;
use Spatie\LaravelData\Data;

class DocumentFolderData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $icon,
        public string $colour,
        public string $colourKey,
        public int $count,
    ) {}

    public static function fromModel(DocumentFolder $folder, int $count): self
    {
        return new self(
            id: $folder->id,
            name: $folder->name,
            icon: $folder->icon,
            colour: $folder->colour->cssVar(),
            colourKey: $folder->colour->value,
            count: $count,
        );
    }
}
