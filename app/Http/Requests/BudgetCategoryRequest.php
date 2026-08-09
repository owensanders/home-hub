<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BudgetCategoryRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:60'],
            'icon' => ['nullable', 'string', 'max:8'],
            'budgeted' => ['required', 'numeric', 'min:0'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'label.required' => 'Give the category a name.',
        ];
    }

    /**
     * The category columns, with the pounds input converted to pence.
     *
     * @return array<string, mixed>
     */
    public function categoryAttributes(): array
    {
        return [
            'label' => trim((string) $this->validated('label')),
            'icon' => $this->validated('icon'),
            'budgeted_pence' => (int) round(((float) $this->validated('budgeted')) * 100),
        ];
    }
}
