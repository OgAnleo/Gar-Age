<?php

namespace Tests\Doubles;

use GarAge\Billing\Domain\Fee;
use GarAge\Billing\Domain\FeeId;
use GarAge\Billing\Domain\FeeRepository;
use GarAge\Billing\Domain\YearMonth;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Test adapter: FeeRepository port kept in memory (no database).
 */
final class InMemoryFeeRepository implements FeeRepository
{
    /** @var array<int, Fee> */
    private array $fees = [];

    private int $nextId = 1;

    public function add(Fee $fee): FeeId
    {
        $id = new FeeId($this->nextId++);
        $this->fees[$id->value] = Fee::restore($id, $fee->tenantId, $fee->contractId, $fee->month, $fee->amount, $fee->paidOn());

        return $id;
    }

    public function save(Fee $fee): void
    {
        $this->fees[$fee->id()->value] = $fee;
    }

    public function find(TenantId $tenantId, FeeId $feeId): ?Fee
    {
        $fee = $this->fees[$feeId->value] ?? null;

        return $fee !== null && $fee->tenantId->equals($tenantId) ? $fee : null;
    }

    public function allOfTenant(TenantId $tenantId): array
    {
        $fees = array_values(array_filter($this->fees, fn (Fee $fee): bool => $fee->tenantId->equals($tenantId)));

        usort($fees, fn (Fee $a, Fee $b): int => strcmp($b->month->value, $a->month->value));

        return $fees;
    }

    public function existsFor(ContractId $contractId, YearMonth $month): bool
    {
        return array_any($this->fees, fn (Fee $fee): bool => $fee->contractId->equals($contractId) && $fee->month->equals($month));
    }

    public function existsForContract(TenantId $tenantId, ContractId $contractId): bool
    {
        return array_any($this->allOfTenant($tenantId), fn (Fee $fee): bool => $fee->contractId->equals($contractId));
    }
}
