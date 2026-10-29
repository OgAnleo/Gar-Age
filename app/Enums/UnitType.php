<?php

namespace App\Enums;

enum UnitType: string
{
    case Garage = 'garage';
    case Storage = 'storage';

    public function label(): string
    {
        return match ($this) {
            self::Garage => 'Garaje',
            self::Storage => 'Trastero',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type) => ['value' => $type->value, 'label' => $type->label()],
            self::cases(),
        );
    }
}
