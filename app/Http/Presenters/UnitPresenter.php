<?php

namespace App\Http\Presenters;

use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitType;

/**
 * Output adapter: turns domain units into the props the React pages expect.
 */
final class UnitPresenter
{
    /**
     * @return array{id: int, code: string, type: string, size_m2: string, type_label: string, daily_price_cents: int}
     */
    public function listItem(Unit $unit): array
    {
        return [
            ...$this->commonFields($unit),
            'type_label' => $unit->details()->type->label(),
            'daily_price_cents' => $unit->details()->dailyPrice->cents(),
        ];
    }

    /**
     * Values for the edit form (prices in euros, as typed by the user).
     *
     * @return array{id: int, code: string, type: string, size_m2: string, daily_price: string}
     */
    public function formValues(Unit $unit): array
    {
        return [
            ...$this->commonFields($unit),
            'daily_price' => $unit->details()->dailyPrice->toDecimalString(),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function typeOptions(): array
    {
        return array_map(
            fn (UnitType $type): array => ['value' => $type->value, 'label' => $type->label()],
            UnitType::cases(),
        );
    }

    /**
     * @return array{id: int, code: string, type: string, size_m2: string}
     */
    private function commonFields(Unit $unit): array
    {
        $details = $unit->details();

        return [
            'id' => $unit->id()->value,
            'code' => $details->code->value,
            'type' => $details->type->value,
            'size_m2' => $details->area->toDecimalString(),
        ];
    }
}
