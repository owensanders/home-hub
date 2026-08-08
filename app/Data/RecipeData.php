<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Recipe;
use Spatie\LaravelData\Data;

class RecipeData extends Data
{
    /** @param list<string> $tags */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public string $meta,
        public ?string $durationLabel,
        public ?string $difficulty,
        public int $tint,
        public array $tags,
        public bool $isFavourite,
    ) {}

    public static function fromModel(Recipe $recipe): self
    {
        return new self(
            id: $recipe->id,
            name: $recipe->name,
            description: $recipe->description,
            meta: implode(' · ', array_filter([$recipe->duration_label, $recipe->difficulty])),
            durationLabel: $recipe->duration_label,
            difficulty: $recipe->difficulty,
            tint: $recipe->tint,
            tags: $recipe->tags ?? [],
            isFavourite: $recipe->is_favourite,
        );
    }
}
