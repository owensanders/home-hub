<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateAiMealPlanRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'people' => ['required', 'integer', 'min:1', 'max:12'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'diets' => ['array'],
            'diets.*' => ['string', 'max:40'],
            'avoid' => ['nullable', 'string', 'max:255'],
            'goals' => ['array'],
            'goals.*' => ['string', 'max:60'],
        ];
    }
}
