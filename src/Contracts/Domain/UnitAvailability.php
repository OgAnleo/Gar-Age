<?php

namespace GarAge\Contracts\Domain;

use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;

/**
 * Domain service: a unit cannot be taken by two contracts on the same day.
 * The repository narrows the candidates by date span; the schedules decide
 * the exact overlap (specific days can interleave without clashing).
 */
final readonly class UnitAvailability
{
    public function __construct(private ContractRepository $contracts) {}

    /**
     * @throws UnitAlreadyRented
     */
    public function ensureFree(TenantId $tenantId, UnitId $unitId, Schedule $schedule, ?ContractId $except = null): void
    {
        foreach ($this->contracts->ofUnitDuring($tenantId, $unitId, $schedule->span(), $except) as $other) {
            if ($other->details()->schedule->overlaps($schedule)) {
                throw UnitAlreadyRented::during($unitId, $schedule->span());
            }
        }
    }
}
