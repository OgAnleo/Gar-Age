<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the name of a new business (its slug is derived by the domain).
 */
class NewBusinessRequest extends FormRequest
{
    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'business_name.required' => 'El nombre del negocio es obligatorio.',
            'business_name.max' => 'El nombre del negocio no puede tener más de :max caracteres.',
        ];
    }
}
