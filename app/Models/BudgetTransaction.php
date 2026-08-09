<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $household_id
 * @property int $budget_category_id
 * @property \Illuminate\Support\Carbon $month
 * @property string $label
 * @property int $amount_pence
 * @property BudgetCategory $category
 */
class BudgetTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\BudgetTransactionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['household_id', 'budget_category_id', 'month', 'label', 'amount_pence'];

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'amount_pence' => 'integer',
        ];
    }

    /** @return BelongsTo<Household, $this> */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /** @return BelongsTo<BudgetCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class, 'budget_category_id');
    }
}
