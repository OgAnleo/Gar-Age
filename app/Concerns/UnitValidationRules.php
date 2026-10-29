<?php

namespace App\Concerns;

use App\Enums\UnitType;
use App\Models\Unit;
use App\Tenancy\CurrentTenant;
use Illuminate\Validation\Rule;

trait UnitValidationRules
{
    /**
     * @return array<string, array<mixed>>
     */
    protected function unitRules(?Unit $ignore = null): array
    {
        return [
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('units')
                    ->where('tenant_id', app(CurrentTenant::class)->id())
                    ->ignore($ignore),
            ],
            'type' => ['required', Rule::enum(UnitType::class)],
            'size_m2' => ['required', 'numeric', 'min:0.5', 'max:9999.99'],
            'monthly_price' => ['required', 'numeric', 'min:0', 'max:99999.99', 'decimal:0,2'],
        ];
    }

    /**
     * Validated data mapped to Unit columns (euros to cents).
     *
     * @return array<string, mixed>
     */
    public function unitData(): array
    {
        return [
            ...$this->safe()->only(['code', 'type', 'size_m2']),
            'monthly_price_cents' => (int) round($this->float('monthly_price') * 100),
        ];
    }
}
