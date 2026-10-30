<?php

namespace GarAge\Units\Domain;

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
