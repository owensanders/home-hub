<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Palette;
use App\Models\Household;
use App\Models\IncomeSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<IncomeSource> */
class IncomeSourceFactory extends Factory
{
    protected $model = IncomeSource::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'label' => $this->faker->words(2, true),
            'meta' => 'Every 4 weeks',
            'colour' => Palette::Mint,
            'amount_pence' => 200000,
            'position' => 0,
        ];
    }
}
