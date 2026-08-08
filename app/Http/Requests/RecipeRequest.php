<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecipeRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration_label' => ['nullable', 'string', 'max:40'],
            'difficulty' => ['nullable', 'string', 'max:40'],
            'tags' => ['array'],
            'tags.*' => ['string', 'max:30'],
            'tint' => ['nullable', 'integer', 'min:0', 'max:4'],
            'is_favourite' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Give the recipe a name.',
        ];
    }

    /**
     * The recipe columns, with the defaults the form leaves off.
     *
     * @return array<string, mixed>
     */
    public function recipeAttributes(): array
    {
        return [
            'name' => trim((string) $this->validated('name')),
            'description' => $this->validated('description'),
            'duration_label' => $this->validated('duration_label'),
            'difficulty' => $this->validated('difficulty'),
            'tags' => $this->validated('tags', []),
            'tint' => $this->validated('tint') ?? 0,
            'is_favourite' => (bool) $this->validated('is_favourite', false),
        ];
    }
}
