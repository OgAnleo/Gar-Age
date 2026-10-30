<?php

namespace GarAge\Units\Application;

use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UniqueUnitCode;
use GarAge\Units\Domain\UnitCodeAlreadyUsed;
use GarAge\Units\Domain\UnitId;
use GarAge\Units\Domain\UnitNotFound;
use GarAge\Units\Domain\UnitRepository;

/**
 * Use case: change the details of a unit, keeping its code unique.
 */
final readonly class UpdateUnit
{
    public function __construct(
        private GetUnit $getUnit,
        private UnitRepository $units,
        private UniqueUnitCode $uniqueCode,
    ) {}

    /**
     * @throws UnitNotFound
     * @throws UnitCodeAlreadyUsed
     */
    public function handle(TenantId $tenantId, UnitId $unitId, UnitInput $input): void
    {
        $unit = $this->getUnit->handle($tenantId, $unitId);
        $details = $input->toDetails();

        $this->uniqueCode->ensureAvailable($tenantId, $details->code, $unitId);

        $unit->changeDetails($details);
        $this->units->save($unit);
    }
}
