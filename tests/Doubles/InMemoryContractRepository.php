<?php

namespace Tests\Doubles;

use DateTimeImmutable;
use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Shared\Domain\DateRange;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;

/**
 * Test adapter: ContractRepository port kept in memory (no database).
 */
final class InMemoryContractRepository implements ContractRepository
{
    /** @var array<int, Contract> */
    private array $contracts = [];

    private int $nextId = 1;

    public function add(Contract $contract): ContractId
    {
        $id = new ContractId($this->nextId++);
        $this->contracts[$id->value] = Contract::restore($id, $contract->tenantId, $contract->details());

        return $id;
    }

    public function save(Contract $contract): void
    {
        $this->contracts[$contract->id()->value] = $contract;
    }

    public function remove(Contract $contract): void
    {
        unset($this->contracts[$contract->id()->value]);
    }

    public function find(TenantId $tenantId, ContractId $contractId): ?Contract
    {
        $contract = $this->contracts[$contractId->value] ?? null;

        return $contract !== null && $contract->tenantId->equals($tenantId) ? $contract : null;
    }

    public function allOfTenant(TenantId $tenantId): array
    {
        $contracts = array_values(array_filter(
            $this->contracts,
            fn (Contract $contract): bool => $contract->tenantId->equals($tenantId),
        ));

        usort($contracts, fn (Contract $a, Contract $b): int => $this->start($b) <=> $this->start($a));

        return $contracts;
    }

    public function ofUnitDuring(TenantId $tenantId, UnitId $unitId, DateRange $period, ?ContractId $except = null): array
    {
        return array_values(array_filter(
            $this->allOfTenant($tenantId),
            fn (Contract $contract): bool => $contract->details()->unitId->equals($unitId)
                && $contract->details()->schedule->span()->overlaps($period)
                && ($except === null || ! $contract->id()->equals($except)),
        ));
    }

    public function startedBy(DateTimeImmutable $day, ?TenantId $tenantId = null): array
    {
        return array_values(array_filter(
            $this->contracts,
            fn (Contract $contract): bool => $this->start($contract) <= $day
                && ($tenantId === null || $contract->tenantId->equals($tenantId)),
        ));
    }

    private function start(Contract $contract): DateTimeImmutable
    {
        return $contract->details()->schedule->span()->start;
    }

    public function existsForUnit(TenantId $tenantId, UnitId $unitId): bool
    {
        return array_any($this->allOfTenant($tenantId), fn (Contract $c): bool => $c->details()->unitId->equals($unitId));
    }

    public function existsForCustomer(TenantId $tenantId, CustomerId $customerId): bool
    {
        return array_any($this->allOfTenant($tenantId), fn (Contract $c): bool => $c->details()->customerId->equals($customerId));
    }
}
