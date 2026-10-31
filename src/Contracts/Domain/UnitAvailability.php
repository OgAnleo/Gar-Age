<?php

namespace GarAge\Contracts\Domain;

use GarAge\Tenancy\Domain\TenantId;

/**
 * Domain service: a unit cannot have two contracts with overlapping dates.
 */
final readonly class UnitAvailability
{
    public function __construct(private ContractRepository $contracts) {}

    /**
     * @throws UnitAlreadyRented
     */
    public function ensureFree(TenantId $tenantId, ContractDetails $details, ?ContractId $except = null): void
    {
        if ($this->contracts->unitBusyDuring($tenantId, $details->unitId, $details->period, $except)) {
            throw UnitAlreadyRented::during($details->unitId, $details->period);
        }
    }
}
