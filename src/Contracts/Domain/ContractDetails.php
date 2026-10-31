<?php

namespace GarAge\Contracts\Domain;

use GarAge\Customers\Domain\CustomerId;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\Money;
use GarAge\Units\Domain\UnitId;

/**
 * Everything that describes a rental contract, validated by its value objects.
 */
final readonly class ContractDetails
{
    public function __construct(
        public UnitId $unitId,
        public CustomerId $customerId,
        public DateRange $period,
        public Money $monthlyPrice,
    ) {}
}
