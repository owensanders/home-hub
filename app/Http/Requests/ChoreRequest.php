<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChoreRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'assigned_to' => [
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
            'name.required' => 'Give the chore a name.',
        ];
    }

    /**
     * The chore columns, with the defaults the form leaves off.
     *
     * @return array<string, mixed>
     */
    public function choreAttributes(): array
    {
        return [
            'name' => trim((string) $this->validated('name')),
            'assigned_to' => $this->validated('assigned_to'),
        ];
    }
}
