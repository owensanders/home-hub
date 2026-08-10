<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\HouseholdRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::enum(HouseholdRole::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Add a name and an email to invite someone',
            'email.required' => 'Add a name and an email to invite someone',
        ];
    }

    /** @return array{name: string, email: string, role: string} */
    public function inviteAttributes(): array
    {
        return [
            'name' => trim((string) $this->validated('name')),
            'email' => (string) $this->validated('email'),
            'role' => (string) $this->validated('role'),
        ];
    }
}
