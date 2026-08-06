<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Palette;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $household_id
 * @property string $name
 * @property string $slug
 * @property \App\Enums\Palette $colour
 * @property int $position
 * @property \Illuminate\Database\Eloquent\Collection<int, ShoppingItem> $items
 */
class ShoppingList extends Model
{
    /** @use HasFactory<\Database\Factories\ShoppingListFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['household_id', 'name', 'slug', 'colour', 'position'];

    protected function casts(): array
    {
        return ['colour' => Palette::class];
    }

    /** @return BelongsTo<Household, $this> */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /** @return HasMany<ShoppingItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ShoppingItem::class)->orderBy('position')->orderBy('id');
    }
}
