<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveBudgetTransactionRequest extends FormRequest
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
        ];
    }
}
