<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\HouseholdRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(HouseholdRole::class)],
        ];
    }
}
