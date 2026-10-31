<?php

namespace GarAge\Customers\Domain;

use GarAge\Tenancy\Domain\TenantId;

/**
 * Port: whether other modules (e.g. contracts) still refer to a customer.
 */
interface CustomerUsage
{
    public function isInUse(TenantId $tenantId, CustomerId $customerId): bool;
}
