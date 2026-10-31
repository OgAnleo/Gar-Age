<?php

namespace GarAge\Contracts\Domain;

use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\DomainError;
use GarAge\Units\Domain\UnitId;

final class UnitAlreadyRented extends DomainError
{
    public static function during(UnitId $unitId, DateRange $period): self
    {
        $end = $period->endString() ?? 'open end';

        return new self("Unit {$unitId->value} already has a contract overlapping {$period->startString()} – {$end}.");
    }
}
