<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Palette;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $household_id
 * @property string $title
 * @property \Illuminate\Support\Carbon $starts_at
 * @property bool $is_all_day
 * @property string|null $who_label
 * @property \App\Enums\Palette $colour
 */
class CalendarEvent extends Model
{
    /** @var list<string> */
    protected $fillable = ['household_id', 'title', 'starts_at', 'is_all_day', 'who_label', 'colour'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'is_all_day' => 'boolean',
            'colour' => Palette::class,
        ];
    }

    /** @return BelongsTo<Household, $this> */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }
}
