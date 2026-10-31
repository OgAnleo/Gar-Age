<?php

namespace App\Http\Requests\Tenant;

use GarAge\Contracts\Application\ContractInput;
use GarAge\Contracts\Domain\RecurringSchedule;
use GarAge\Contracts\Domain\SpecificDaysSchedule;
use GarAge\Pricing\Domain\BookingMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Input adapter for creating and updating contracts: checks the shape of the
 * form data. Recurring modes send a start date and an optional number of
 * periods; the "days" mode sends a list of dates. Business rules (unit and
 * customer of the tenant, no overlapping bookings, price) live in the domain.
 */
class ContractRequest extends FormRequest
{
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
            'mode' => ['required', Rule::enum(BookingMode::class)],
            'starts_on' => ['exclude_if:mode,days', 'required', 'date_format:Y-m-d'],
            'periods' => ['exclude_if:mode,days', 'nullable', 'integer', 'min:1', 'max:'.RecurringSchedule::MAX_PERIODS],
            'days' => ['exclude_unless:mode,days', 'required', 'array', 'max:'.SpecificDaysSchedule::MAX_DAYS],
            'days.*' => ['exclude_unless:mode,days', 'date_format:Y-m-d', 'distinct'],
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
            'mode.required' => 'Elige una modalidad.',
            'mode.*' => 'La modalidad no es válida.',
            'starts_on.required' => 'La fecha de inicio es obligatoria.',
            'starts_on.date_format' => 'La fecha de inicio no es válida.',
            'periods.*' => 'La duración debe ser un número entero entre 1 y '.RecurringSchedule::MAX_PERIODS.'.',
            'days.required' => 'Añade al menos un día.',
            'days.max' => 'Se pueden reservar como máximo :max días.',
            'days.*.date_format' => 'Alguno de los días no es una fecha válida.',
            'days.*.distinct' => 'Hay días repetidos.',
        ];
    }

    public function contractInput(): ContractInput
    {
        return new ContractInput(
            unitId: $this->integer('unit_id'),
            customerId: $this->integer('customer_id'),
            mode: $this->string('mode')->toString(),
            startsOn: $this->filled('starts_on') ? $this->string('starts_on')->toString() : null,
            periods: $this->filled('periods') ? $this->integer('periods') : null,
            days: array_values(array_filter($this->array('days'), is_string(...))),
        );
    }
}
