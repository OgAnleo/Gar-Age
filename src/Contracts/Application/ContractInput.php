<?php

namespace GarAge\Contracts\Application;

use GarAge\Contracts\Domain\ContractDetails;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Shared\Domain\Money;
use GarAge\Units\Domain\UnitId;

/**
 * Raw data for creating or updating a contract, as it arrives from outside.
 * Dates are "Y-m-d"; a null end date means the contract has no end yet.
 */
final readonly class ContractInput
{
    public function __construct(
        public int $unitId,
        public int $customerId,
        public string $startsOn,
        public ?string $endsOn,
        public string $monthlyPrice,
    ) {}

    /**
     * @throws InvalidValue
     */
    public function toDetails(): ContractDetails
    {
        return new ContractDetails(
            new UnitId($this->unitId),
            new CustomerId($this->customerId),
            DateRange::fromStrings($this->startsOn, $this->endsOn),
            Money::fromString($this->monthlyPrice),
        );
    }
}
