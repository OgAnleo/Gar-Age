<?php

namespace Tests\Doubles;

use DateTimeImmutable;
use GarAge\Billing\Domain\Fee;
use GarAge\Billing\Domain\FeeId;
use GarAge\Billing\Domain\FeeRepository;
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
        $this->fees[$id->value] = Fee::restore($id, $fee->tenantId, $fee->contractId, $fee->period, $fee->amount, $fee->paidOn());

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

        usort($fees, fn (Fee $a, Fee $b): int => $b->period->start <=> $a->period->start);

        return $fees;
    }

    public function existsFor(ContractId $contractId, DateTimeImmutable $periodStart): bool
    {
        return array_any($this->fees, fn (Fee $fee): bool => $fee->contractId->equals($contractId)
            && $fee->period->startString() === $periodStart->format('Y-m-d'));
    }

    public function existsForContract(TenantId $tenantId, ContractId $contractId): bool
    {
        return array_any($this->allOfTenant($tenantId), fn (Fee $fee): bool => $fee->contractId->equals($contractId));
    }
}
