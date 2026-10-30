<?php

namespace GarAge\Units\Application;

use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UniqueUnitCode;
use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitCodeAlreadyUsed;
use GarAge\Units\Domain\UnitId;
use GarAge\Units\Domain\UnitRepository;

/**
 * Use case: add a unit to a tenant.
 */
final readonly class CreateUnit
{
    public function __construct(
        private UnitRepository $units,
        private UniqueUnitCode $uniqueCode,
    ) {}

    /**
     * @throws UnitCodeAlreadyUsed
     */
    public function handle(TenantId $tenantId, UnitInput $input): UnitId
    {
        $details = $input->toDetails();

        $this->uniqueCode->ensureAvailable($tenantId, $details->code);

        return $this->units->add(Unit::create($tenantId, $details));
    }
}
