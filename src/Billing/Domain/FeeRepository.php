<?php

namespace GarAge\Billing\Domain;

use DateTimeImmutable;
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
     * @return list<Fee> Newest period first.
     */
    public function allOfTenant(TenantId $tenantId): array;

    /**
     * Whether the contract already has the fee of the period starting that day (contract ids are global).
     */
    public function existsFor(ContractId $contractId, DateTimeImmutable $periodStart): bool;

    public function existsForContract(TenantId $tenantId, ContractId $contractId): bool;
}
