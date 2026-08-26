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
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::enum(HouseholdRole::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.required' => 'Add an email to invite someone',
        ];
    }

    /** @return array{email: string, role: string} */
    public function inviteAttributes(): array
    {
        return [
            'email' => (string) $this->validated('email'),
            'role' => (string) $this->validated('role'),
        ];
    }
}
