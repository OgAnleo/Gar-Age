<?php

namespace App\Http\Requests\Tenant;

use App\Concerns\UnitValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    use UnitValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return $this->unitRules();
    }
}
