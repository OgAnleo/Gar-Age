<?php

namespace GarAge\Contracts\Infrastructure;

use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;
use GarAge\Units\Domain\UnitUsage;

/**
 * Adapter: a unit is in use while it has contracts.
 */
final readonly class ContractsUnitUsage implements UnitUsage
{
    public function __construct(private ContractRepository $contracts) {}

    public function isInUse(TenantId $tenantId, UnitId $unitId): bool
    {
        return $this->contracts->existsForUnit($tenantId, $unitId);
    }
}
