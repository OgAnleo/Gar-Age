<?php

namespace Tests\Doubles;

use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitCode;
use GarAge\Units\Domain\UnitId;
use GarAge\Units\Domain\UnitRepository;

/**
 * Test adapter: UnitRepository port kept in memory (no database).
 */
final class InMemoryUnitRepository implements UnitRepository
{
    /** @var array<int, Unit> */
    private array $units = [];

    private int $nextId = 1;

    public function add(Unit $unit): UnitId
    {
        $id = new UnitId($this->nextId++);
        $this->units[$id->value] = Unit::restore($id, $unit->tenantId, $unit->details());

        return $id;
    }

    public function save(Unit $unit): void
    {
        $this->units[$unit->id()->value] = $unit;
    }

    public function remove(Unit $unit): void
    {
        unset($this->units[$unit->id()->value]);
    }

    public function find(TenantId $tenantId, UnitId $unitId): ?Unit
    {
        $unit = $this->units[$unitId->value] ?? null;

        return $unit !== null && $unit->tenantId->equals($tenantId) ? $unit : null;
    }

    public function allOfTenant(TenantId $tenantId): array
    {
        $units = array_values(array_filter(
            $this->units,
            fn (Unit $unit): bool => $unit->tenantId->equals($tenantId),
        ));

        usort($units, fn (Unit $a, Unit $b): int => strcmp($a->details()->code->value, $b->details()->code->value));

        return $units;
    }

    public function codeTaken(TenantId $tenantId, UnitCode $code, ?UnitId $except = null): bool
    {
        foreach ($this->allOfTenant($tenantId) as $unit) {
            if ($unit->details()->code->equals($code) && ($except === null || ! $unit->id()->equals($except))) {
                return true;
            }
        }

        return false;
    }
}
