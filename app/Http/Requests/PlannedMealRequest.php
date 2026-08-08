<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\MealSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlannedMealRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'recipe_id' => [
                'required_without:new_recipe_name',
                'nullable',
                'integer',
                Rule::exists('recipes', 'id')->where('household_id', $this->user()?->household_id),
            ],
            'new_recipe_name' => ['required_without:recipe_id', 'nullable', 'string', 'max:120'],
            'new_recipe_description' => ['nullable', 'string', 'max:2000'],
            'planned_on' => ['required', 'date'],
            'slot' => ['required', Rule::enum(MealSlot::class)],
            'cook_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('household_id', $this->user()?->household_id),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'recipe_id.required_without' => 'Pick a recipe or name a new one.',
            'new_recipe_name.required_without' => 'Pick a recipe or name a new one.',
            'planned_on.required' => 'Choose a day for this meal.',
        ];
    }

    /**
     * The planned-meal columns, with the recipe left for the repository to
     * resolve (an existing id, or create-on-the-fly from a new name).
     *
     * @return array<string, mixed>
     */
    public function plannedMealAttributes(): array
    {
        return [
            'recipe_id' => $this->validated('recipe_id'),
            'new_recipe_name' => trim((string) $this->validated('new_recipe_name', '')) ?: null,
            'new_recipe_description' => trim((string) $this->validated('new_recipe_description', '')) ?: null,
            'planned_on' => $this->validated('planned_on'),
            'slot' => MealSlot::from($this->validated('slot')),
            'cook_id' => $this->validated('cook_id'),
        ];
    }
}
