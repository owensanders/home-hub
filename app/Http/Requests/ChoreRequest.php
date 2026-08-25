<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ChoreRepeat;
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
            'due_date' => ['required', 'date'],
            'repeat' => ['nullable', Rule::enum(ChoreRepeat::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Give the chore a name.',
            'due_date.required' => 'Give the chore a start date.',
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
            'due_date' => $this->validated('due_date'),
            'repeat' => $this->validated('repeat') ?? ChoreRepeat::None->value,
        ];
    }
}
