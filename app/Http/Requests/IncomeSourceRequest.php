<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncomeSourceRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:60'],
            'meta' => ['nullable', 'string', 'max:60'],
            'amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'label.required' => 'Give the income source a name.',
        ];
    }

    /**
     * The income source columns, with the pounds input converted to pence.
     *
     * @return array<string, mixed>
     */
    public function incomeAttributes(): array
    {
        return [
            'label' => trim((string) $this->validated('label')),
            'meta' => $this->validated('meta'),
            'amount_pence' => (int) round(((float) $this->validated('amount')) * 100),
        ];
    }
}
