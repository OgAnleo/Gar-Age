<?php

namespace GarAge\Units\Application;

use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;
use GarAge\Units\Domain\UnitNotFound;
use GarAge\Units\Domain\UnitRepository;

/**
 * Use case: remove a unit of a tenant.
 */
final readonly class DeleteUnit
{
    public function __construct(
        private GetUnit $getUnit,
        private UnitRepository $units,
    ) {}

    /**
     * @throws UnitNotFound
     */
    public function handle(TenantId $tenantId, UnitId $unitId): void
    {
        $this->units->remove($this->getUnit->handle($tenantId, $unitId));
    }
}
