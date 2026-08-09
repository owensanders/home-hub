<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Palette;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $household_id
 * @property string $label
 * @property string|null $meta
 * @property \App\Enums\Palette $colour
 * @property int $amount_pence
 * @property int $position
 */
class IncomeSource extends Model
{
    /** @use HasFactory<\Database\Factories\IncomeSourceFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['household_id', 'label', 'meta', 'colour', 'amount_pence', 'position'];

    protected function casts(): array
    {
        return [
            'colour' => Palette::class,
            'amount_pence' => 'integer',
        ];
    }

    /** @return BelongsTo<Household, $this> */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }
}
