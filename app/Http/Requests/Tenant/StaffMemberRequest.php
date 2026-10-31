<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the shape of a new employee; whether they already belong to the
 * business is a domain rule.
 */
class StaffMemberRequest extends FormRequest
{
    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de :max caracteres.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email no es válido.',
            'email.max' => 'El email no puede tener más de :max caracteres.',
        ];
    }
}
