<?php

namespace App\Http\Requests\Tenant;

use App\Concerns\UnitValidationRules;
use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    use UnitValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        /** @var Unit $unit */
        $unit = $this->route('unit');

        return $this->unitRules($unit);
    }
}
