<?php

namespace App\Http\Requests\Tenant;

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
            'monthly_price' => ['required', 'numeric', 'min:0', 'max:99999.99', 'decimal:0,2'],
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
