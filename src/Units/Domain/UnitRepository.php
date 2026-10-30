<?php

namespace GarAge\Units\Domain;

use GarAge\Tenancy\Domain\TenantId;

/**
 * Port: persistence of units. Every read is scoped to one tenant.
 */
interface UnitRepository
{
    public function add(Unit $unit): UnitId;

    public function save(Unit $unit): void;

    public function remove(Unit $unit): void;

    public function find(TenantId $tenantId, UnitId $unitId): ?Unit;

    /**
     * @return list<Unit> Ordered by code.
     */
    public function allOfTenant(TenantId $tenantId): array;

    public function codeTaken(TenantId $tenantId, UnitCode $code, ?UnitId $except = null): bool;
}
