<?php

namespace GarAge\Units\Application;

use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitId;
use GarAge\Units\Domain\UnitNotFound;
use GarAge\Units\Domain\UnitRepository;

/**
 * Use case: one unit of a tenant. Units of other tenants are "not found".
 */
final readonly class GetUnit
{
    public function __construct(private UnitRepository $units) {}

    /**
     * @throws UnitNotFound
     */
    public function handle(TenantId $tenantId, UnitId $unitId): Unit
    {
        return $this->units->find($tenantId, $unitId) ?? throw UnitNotFound::withId($unitId);
    }
}
