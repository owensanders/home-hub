<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ShoppingCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShoppingItemRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'quantity' => ['nullable', 'string', 'max:40'],
            'category' => ['nullable', Rule::enum(ShoppingCategory::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Give the item a name.',
        ];
    }
}
