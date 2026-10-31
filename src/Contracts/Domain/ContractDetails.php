<?php

namespace GarAge\Contracts\Domain;

use GarAge\Customers\Domain\CustomerId;
use GarAge\Shared\Domain\Money;
use GarAge\Units\Domain\UnitId;

/**
 * Everything that describes a rental contract. The price per billing period
 * is fixed when the contract is made: later changes to the business pricing
 * conditions do not affect it.
 */
final readonly class ContractDetails
{
    public function __construct(
        public UnitId $unitId,
        public CustomerId $customerId,
        public Schedule $schedule,
        public Money $pricePerPeriod,
    ) {}

    public function withPrice(Money $pricePerPeriod): self
    {
        return new self($this->unitId, $this->customerId, $this->schedule, $pricePerPeriod);
    }
}
