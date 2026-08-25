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
            'plan' => ['nullable', 'string', Rule::in(array_keys(config('plans')))],
            'cycle' => ['required_with:plan', Rule::in(['monthly', 'annual'])],
            'invites' => ['array'],
            'invites.*.email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'invites.*.role' => ['required', Rule::enum(HouseholdRole::class)],
        ];
    }

    /** @return array{name: string, address: string|null, size: string|null, plan: string|null, cycle: string|null, invites: list<array{email: string, role: string}>} */
    public function householdAttributes(): array
    {
        return [
            'name' => (string) $this->validated('name'),
            'address' => $this->validated('address'),
            'size' => $this->validated('size'),
            'plan' => $this->validated('plan'),
            'cycle' => $this->validated('cycle'),
            'invites' => $this->validated('invites', []),
        ];
    }
}
