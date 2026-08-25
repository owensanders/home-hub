<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlanUpdateRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'plan' => ['required', 'string', Rule::in(array_keys(config('plans')))],
            'cycle' => ['required', Rule::in(['monthly', 'annual'])],
        ];
    }
}
