<?php

namespace GarAge\Units\Application;

use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;
use GarAge\Units\Domain\UnitInUse;
use GarAge\Units\Domain\UnitNotFound;
use GarAge\Units\Domain\UnitRepository;
use GarAge\Units\Domain\UnitUsage;

/**
 * Use case: remove a unit of a tenant, unless something still refers to it.
 */
final readonly class DeleteUnit
{
    public function __construct(
        private GetUnit $getUnit,
        private UnitRepository $units,
        private UnitUsage $usage,
    ) {}

    /**
     * @throws UnitNotFound
     * @throws UnitInUse
     */
    public function handle(TenantId $tenantId, UnitId $unitId): void
    {
        $unit = $this->getUnit->handle($tenantId, $unitId);

        if ($this->usage->isInUse($tenantId, $unitId)) {
            throw UnitInUse::withId($unitId);
        }

        $this->units->remove($unit);
    }
}
