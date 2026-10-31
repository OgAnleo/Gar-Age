<?php

namespace Tests\Doubles;

use GarAge\Customers\Domain\CustomerId;
use GarAge\Customers\Domain\CustomerUsage;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;
use GarAge\Units\Domain\UnitUsage;

/**
 * Test adapter: usage ports answering a fixed value.
 */
final class FakeUsage implements CustomerUsage, UnitUsage
{
    public function __construct(public bool $inUse = false) {}

    public function isInUse(TenantId $tenantId, CustomerId|UnitId $id): bool
    {
        return $this->inUse;
    }
}
