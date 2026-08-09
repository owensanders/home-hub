<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BudgetTransactionRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'budget_category_id' => [
                'required',
                'integer',
                Rule::exists('budget_categories', 'id')->where('household_id', $this->user()?->household_id),
            ],
            'label' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'label.required' => 'Give the spend a label.',
        ];
    }

    /**
     * The transaction columns, with the pounds input converted to pence.
     *
     * @return array<string, mixed>
     */
    public function transactionAttributes(): array
    {
        return [
            'label' => trim((string) $this->validated('label')),
            'amount_pence' => (int) round(((float) $this->validated('amount')) * 100),
        ];
    }
}
