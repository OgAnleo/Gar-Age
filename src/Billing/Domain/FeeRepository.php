<?php

namespace GarAge\Billing\Domain;

use GarAge\Contracts\Domain\ContractId;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Port: persistence of fees. Reads for the UI are scoped to one tenant.
 */
interface FeeRepository
{
    public function add(Fee $fee): FeeId;

    public function save(Fee $fee): void;

    public function find(TenantId $tenantId, FeeId $feeId): ?Fee;

    /**
     * @return list<Fee> Newest month first.
     */
    public function allOfTenant(TenantId $tenantId): array;

    /**
     * Whether the contract already has its fee for the month (contract ids are global).
     */
    public function existsFor(ContractId $contractId, YearMonth $month): bool;

    public function existsForContract(TenantId $tenantId, ContractId $contractId): bool;
}
