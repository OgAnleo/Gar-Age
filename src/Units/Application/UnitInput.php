<?php

namespace GarAge\Units\Application;

use GarAge\Shared\Domain\InvalidValue;
use GarAge\Shared\Domain\Money;
use GarAge\Units\Domain\Area;
use GarAge\Units\Domain\UnitCode;
use GarAge\Units\Domain\UnitDetails;
use GarAge\Units\Domain\UnitType;

/**
 * Raw data for creating or updating a unit, as it arrives from outside.
 */
final readonly class UnitInput
{
    public function __construct(
        public string $code,
        public string $type,
        public string $sizeM2,
        public string $monthlyPrice,
    ) {}

    /**
     * @throws InvalidValue
     */
    public function toDetails(): UnitDetails
    {
        return new UnitDetails(
            new UnitCode($this->code),
            UnitType::tryFrom($this->type) ?? throw new InvalidValue("Unknown unit type \"{$this->type}\"."),
            Area::fromString($this->sizeM2),
            Money::fromString($this->monthlyPrice),
        );
    }
}
