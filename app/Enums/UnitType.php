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
}
