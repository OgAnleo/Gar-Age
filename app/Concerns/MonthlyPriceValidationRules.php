<?php

namespace App\Concerns;

/**
 * Validation of the "monthly_price" field (euros with up to two decimals),
 * shared by the unit and contract forms.
 */
trait MonthlyPriceValidationRules
{
    /**
     * @return array<int, string>
     */
    protected function monthlyPriceRules(): array
    {
        return ['required', 'numeric', 'min:0', 'max:99999.99', 'decimal:0,2'];
    }

    /**
     * @return array<string, string>
     */
    protected function monthlyPriceMessages(): array
    {
        return [
            'monthly_price.required' => 'El precio mensual es obligatorio.',
            'monthly_price.numeric' => 'El precio mensual debe ser un número.',
            'monthly_price.min' => 'El precio mensual no puede ser negativo.',
            'monthly_price.max' => 'El precio mensual máximo es :max €.',
            'monthly_price.decimal' => 'El precio mensual admite como máximo 2 decimales.',
        ];
    }
}
