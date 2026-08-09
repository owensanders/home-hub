<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\BudgetTransaction;
use App\Support\Money;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class BudgetTransactionData extends Data
{
    public function __construct(
        public int $id,
        public string $label,
        public string $amount,
        public int $amountPence,
        public int $categoryId,
        public string $categoryLabel,
        public string $categoryColour,
        public string $meta,
    ) {}

    public static function fromModel(BudgetTransaction $transaction): self
    {
        return new self(
            id: $transaction->id,
            label: $transaction->label,
            amount: Money::format($transaction->amount_pence),
            amountPence: $transaction->amount_pence,
            categoryId: $transaction->category->id,
            categoryLabel: $transaction->category->label,
            categoryColour: $transaction->category->colour->cssVar(),
            meta: self::relativeDate($transaction->created_at ?? Carbon::now()),
        );
    }

    private static function relativeDate(Carbon $date): string
    {
        return match (true) {
            $date->isToday() => 'Today',
            $date->isYesterday() => 'Yesterday',
            default => $date->format('j M'),
        };
    }
}
