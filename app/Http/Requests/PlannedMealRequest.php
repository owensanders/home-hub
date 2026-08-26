<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\MealSlot;
use App\Enums\RecipeTag;
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
            'new_recipe_name' => [
                'required_without:recipe_id',
                'nullable',
                'string',
                'max:120',
                Rule::unique('recipes', 'name')->where('household_id', $this->user()?->household_id),
            ],
            'new_recipe_description' => ['nullable', 'string', 'max:2000'],
            'new_recipe_duration_label' => ['nullable', 'string', 'max:40'],
            'new_recipe_difficulty' => ['nullable', 'string', 'max:40'],
            'new_recipe_tags' => ['array'],
            'new_recipe_tags.*' => [Rule::enum(RecipeTag::class)],
            'new_recipe_ingredients' => ['array'],
            'new_recipe_ingredients.*.name' => ['required', 'string', 'max:120'],
            'new_recipe_ingredients.*.quantity' => ['nullable', 'string', 'max:40'],
            'new_recipe_tint' => ['nullable', 'integer', 'min:0', 'max:4'],
            'new_recipe_is_favourite' => ['boolean'],
            'new_recipe_shopping_list_id' => [
                'nullable',
                'integer',
                Rule::exists('shopping_lists', 'id')->where('household_id', $this->user()?->household_id),
            ],
            'planned_on' => ['required', 'date'],
            'slot' => ['required', Rule::enum(MealSlot::class)],
            'cook_id' => [
                'nullable',
                'integer',
                Rule::exists('household_user', 'user_id')->where('household_id', $this->user()?->current_household_id)->where('pending', false),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'recipe_id.required_without' => 'Pick a recipe or name a new one.',
            'new_recipe_name.required_without' => 'Pick a recipe or name a new one.',
            'new_recipe_name.unique' => 'You already have a recipe with this name.',
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
            'new_recipe_duration_label' => $this->validated('new_recipe_duration_label'),
            'new_recipe_difficulty' => $this->validated('new_recipe_difficulty'),
            'new_recipe_tags' => $this->validated('new_recipe_tags', []),
            'new_recipe_ingredients' => $this->validated('new_recipe_ingredients', []),
            'new_recipe_tint' => $this->validated('new_recipe_tint') ?? 0,
            'new_recipe_is_favourite' => (bool) $this->validated('new_recipe_is_favourite', false),
            'new_recipe_shopping_list_id' => $this->validated('new_recipe_shopping_list_id'),
            'planned_on' => $this->validated('planned_on'),
            'slot' => MealSlot::from($this->validated('slot')),
            'cook_id' => $this->validated('cook_id'),
        ];
    }
}
