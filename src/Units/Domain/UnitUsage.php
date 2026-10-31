<?php

namespace GarAge\Units\Domain;

use GarAge\Tenancy\Domain\TenantId;

/**
 * Port: whether other modules (e.g. contracts) still refer to a unit.
 */
interface UnitUsage
{
    public function isInUse(TenantId $tenantId, UnitId $unitId): bool;
}
