<?php

namespace GarAge\Contracts\Domain;

enum ContractStatus: string
{
    case Upcoming = 'upcoming';
    case Active = 'active';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::Upcoming => 'Próximo',
            self::Active => 'Activo',
            self::Finished => 'Finalizado',
        };
    }
}
