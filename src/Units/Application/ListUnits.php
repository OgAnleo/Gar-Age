<?php

namespace GarAge\Units\Application;

use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitRepository;

/**
 * Use case: the units of a tenant, ordered by code.
 */
final readonly class ListUnits
{
    public function __construct(private UnitRepository $units) {}

    /**
     * @return list<Unit>
     */
    public function handle(TenantId $tenantId): array
    {
        return $this->units->allOfTenant($tenantId);
    }
}
