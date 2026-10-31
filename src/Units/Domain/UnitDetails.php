<?php

namespace GarAge\Units\Domain;

use GarAge\Shared\Domain\Money;

/**
 * Everything that describes a unit, validated by its value objects.
 */
final readonly class UnitDetails
{
    public function __construct(
        public UnitCode $code,
        public UnitType $type,
        public Area $area,
        public Money $monthlyPrice,
    ) {}
}
