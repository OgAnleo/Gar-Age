<?php

namespace GarAge\Contracts\Domain;

use GarAge\Customers\Domain\CustomerId;
use GarAge\Shared\Domain\DateRange;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;

/**
 * Port: persistence of contracts. Every read is scoped to one tenant.
 */
interface ContractRepository
{
    public function add(Contract $contract): ContractId;

    public function save(Contract $contract): void;

    public function remove(Contract $contract): void;

    public function find(TenantId $tenantId, ContractId $contractId): ?Contract;

    /**
     * @return list<Contract> Newest start date first.
     */
    public function allOfTenant(TenantId $tenantId): array;

    /**
     * Whether another contract of the unit overlaps the period.
     */
    public function unitBusyDuring(TenantId $tenantId, UnitId $unitId, DateRange $period, ?ContractId $except = null): bool;

    /**
     * Contracts running at some point of the period, of every tenant unless
     * one is given (used by scheduled jobs such as fee generation).
     *
     * @return list<Contract>
     */
    public function overlapping(DateRange $period, ?TenantId $tenantId = null): array;

    public function existsForUnit(TenantId $tenantId, UnitId $unitId): bool;

    public function existsForCustomer(TenantId $tenantId, CustomerId $customerId): bool;
}
