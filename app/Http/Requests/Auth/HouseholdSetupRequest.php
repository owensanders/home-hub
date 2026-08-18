<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Enums\HouseholdRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HouseholdSetupRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:32'],
            'invites' => ['array'],
            'invites.*.email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'invites.*.role' => ['required', Rule::enum(HouseholdRole::class)],
        ];
    }

    /** @return array{name: string, address: string|null, size: string|null, invites: list<array{email: string, role: string}>} */
    public function householdAttributes(): array
    {
        return [
            'name' => (string) $this->validated('name'),
            'address' => $this->validated('address'),
            'size' => $this->validated('size'),
            'invites' => $this->validated('invites', []),
        ];
    }
}
