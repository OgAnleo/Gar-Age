<?php

namespace App\Http\Requests\Tenant;

use App\Enums\UnitType;
use App\Tenancy\CurrentTenant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('units')->where('tenant_id', app(CurrentTenant::class)->id()),
            ],
            'type' => ['required', Rule::enum(UnitType::class)],
            'size_m2' => ['required', 'numeric', 'min:0.5', 'max:9999.99'],
            'monthly_price' => ['required', 'numeric', 'min:0', 'max:99999.99', 'decimal:0,2'],
        ];
    }
}
