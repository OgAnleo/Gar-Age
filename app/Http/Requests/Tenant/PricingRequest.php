<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Input adapter for the business pricing conditions: whole discount percentages.
 */
class PricingRequest extends FormRequest
{
    private const array FIELDS = [
        'weekly_discount' => 'semanal',
        'monthly_discount' => 'mensual',
        'annual_discount' => 'anual',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return array_map(fn () => ['required', 'integer', 'min:0', 'max:100'], self::FIELDS);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [];
        foreach (self::FIELDS as $field => $mode) {
            $messages["{$field}.*"] = "El descuento {$mode} debe ser un número entero entre 0 y 100.";
        }

        return $messages;
    }

    public function weekly(): int
    {
        return $this->integer('weekly_discount');
    }

    public function monthly(): int
    {
        return $this->integer('monthly_discount');
    }

    public function annual(): int
    {
        return $this->integer('annual_discount');
    }
}
