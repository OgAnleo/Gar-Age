<?php

namespace GarAge\Tenancy\Domain;

/**
 * Port: removes a tenant together with everything it owns (units, customers,
 * contracts, fees, members), in an order the safety foreign keys accept.
 */
interface TenantEraser
{
    public function erase(TenantId $tenantId): void;
}
