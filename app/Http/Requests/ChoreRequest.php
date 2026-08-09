<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ChoreStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChoreRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'status' => ['required', Rule::enum(ChoreStatus::class)],
            'due_label' => ['nullable', 'string', 'max:60'],
            'repeat_label' => ['nullable', 'string', 'max:60'],
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
            'status' => $this->validated('status'),
            'due_label' => $this->validated('due_label'),
            'repeat_label' => $this->validated('repeat_label'),
        ];
    }
}
