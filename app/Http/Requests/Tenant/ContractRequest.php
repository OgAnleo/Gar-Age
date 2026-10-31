<?php

namespace App\Http\Requests\Tenant;

use App\Concerns\MonthlyPriceValidationRules;
use GarAge\Contracts\Application\ContractInput;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Input adapter for creating and updating contracts: checks the shape of the
 * form data. Business rules (the unit and customer belong to the tenant, no
 * overlapping contracts per unit) live in the domain.
 */
class ContractRequest extends FormRequest
{
    use MonthlyPriceValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'integer', 'min:1'],
            'customer_id' => ['required', 'integer', 'min:1'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'monthly_price' => $this->monthlyPriceRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'unit_id.required' => 'Elige una unidad.',
            'unit_id.*' => 'La unidad no es válida.',
            'customer_id.required' => 'Elige un cliente.',
            'customer_id.*' => 'El cliente no es válido.',
            'starts_on.required' => 'La fecha de inicio es obligatoria.',
            'starts_on.date_format' => 'La fecha de inicio no es válida.',
            'ends_on.date_format' => 'La fecha de fin no es válida.',
            'ends_on.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
            ...$this->monthlyPriceMessages(),
        ];
    }

    public function contractInput(): ContractInput
    {
        return new ContractInput(
            unitId: $this->integer('unit_id'),
            customerId: $this->integer('customer_id'),
            startsOn: $this->string('starts_on')->toString(),
            endsOn: $this->filled('ends_on') ? $this->string('ends_on')->toString() : null,
            monthlyPrice: $this->string('monthly_price')->toString(),
        );
    }
}
