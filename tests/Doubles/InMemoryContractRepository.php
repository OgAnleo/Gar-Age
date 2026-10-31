<?php

namespace Tests\Doubles;

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

        usort($contracts, fn (Contract $a, Contract $b): int => $b->details()->period->start <=> $a->details()->period->start);

        return $contracts;
    }

    public function unitBusyDuring(TenantId $tenantId, UnitId $unitId, DateRange $period, ?ContractId $except = null): bool
    {
        foreach ($this->allOfTenant($tenantId) as $contract) {
            $details = $contract->details();

            if ($details->unitId->equals($unitId) && $details->period->overlaps($period)
                && ($except === null || ! $contract->id()->equals($except))) {
                return true;
            }
        }

        return false;
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
