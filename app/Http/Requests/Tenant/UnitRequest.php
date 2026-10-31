<?php

namespace App\Http\Requests\Tenant;

use App\Concerns\MonthlyPriceValidationRules;
use GarAge\Units\Application\UnitInput;
use GarAge\Units\Domain\UnitCode;
use GarAge\Units\Domain\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Input adapter for creating and updating units: checks the shape of the form
 * data. Business rules (unique code per tenant) live in the domain.
 */
class UnitRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:'.UnitCode::MAX_LENGTH],
            'type' => ['required', Rule::enum(UnitType::class)],
            'size_m2' => ['required', 'numeric', 'min:0.5', 'max:9999.99', 'decimal:0,2'],
            'monthly_price' => $this->monthlyPriceRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'El código es obligatorio.',
            'code.max' => 'El código no puede tener más de :max caracteres.',
            'type.required' => 'Elige un tipo de unidad.',
            'type.enum' => 'El tipo de unidad no es válido.',
            'size_m2.required' => 'La superficie es obligatoria.',
            'size_m2.numeric' => 'La superficie debe ser un número.',
            'size_m2.min' => 'La superficie mínima es :min m².',
            'size_m2.max' => 'La superficie máxima es :max m².',
            'size_m2.decimal' => 'La superficie admite como máximo 2 decimales.',
            ...$this->monthlyPriceMessages(),
        ];
    }

    public function unitInput(): UnitInput
    {
        return new UnitInput(
            code: $this->string('code')->toString(),
            type: $this->string('type')->toString(),
            sizeM2: $this->string('size_m2')->toString(),
            monthlyPrice: $this->string('monthly_price')->toString(),
        );
    }
}
