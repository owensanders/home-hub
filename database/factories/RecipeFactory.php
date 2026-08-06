<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Household;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Recipe> */
class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'duration_label' => '30 min',
            'difficulty' => 'Easy',
            'tags' => [],
            'tint' => $this->faker->numberBetween(0, 4),
            'is_favourite' => false,
        ];
    }
}
