<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'key' => ['required', Rule::in(['approve', 'kidsSpend', 'choreNudge', 'guest'])],
        ];
    }
}
