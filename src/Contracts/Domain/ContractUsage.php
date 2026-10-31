<?php

namespace GarAge\Contracts\Domain;

use GarAge\Tenancy\Domain\TenantId;

/**
 * Port: whether other modules (e.g. billing) still refer to a contract.
 */
interface ContractUsage
{
    public function isInUse(TenantId $tenantId, ContractId $contractId): bool;
}
