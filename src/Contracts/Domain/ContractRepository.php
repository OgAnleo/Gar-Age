<?php

namespace GarAge\Contracts\Domain;

use DateTimeImmutable;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Shared\Domain\DateRange;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;

/**
 * Port: persistence of contracts. Reads for the UI are scoped to one tenant.
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
     * Other contracts of the unit whose span overlaps the period (candidates
     * for a clash; their schedules decide whether they really overlap).
     *
     * @return list<Contract>
     */
    public function ofUnitDuring(TenantId $tenantId, UnitId $unitId, DateRange $period, ?ContractId $except = null): array;

    /**
     * Contracts that have started by the day, of every tenant unless one is
     * given (used by scheduled jobs such as fee generation).
     *
     * @return list<Contract>
     */
    public function startedBy(DateTimeImmutable $day, ?TenantId $tenantId = null): array;

    public function existsForUnit(TenantId $tenantId, UnitId $unitId): bool;

    public function existsForCustomer(TenantId $tenantId, CustomerId $customerId): bool;
}
