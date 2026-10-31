<?php

namespace GarAge\Billing\Domain;

use DateTimeImmutable;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;
use LogicException;

/**
 * What a contract owes for one month, and whether it has been paid.
 * A fee becomes overdue once its month is over without being paid.
 */
final class Fee
{
    private function __construct(
        private readonly ?FeeId $id,
        public readonly TenantId $tenantId,
        public readonly ContractId $contractId,
        public readonly YearMonth $month,
        public readonly Money $amount,
        private ?DateTimeImmutable $paidOn,
    ) {}

    /**
     * A new unpaid fee, not persisted yet (the repository assigns its id).
     */
    public static function issue(TenantId $tenantId, ContractId $contractId, YearMonth $month, Money $amount): self
    {
        return new self(null, $tenantId, $contractId, $month, $amount, null);
    }

    /**
     * A fee rebuilt from storage.
     */
    public static function restore(
        FeeId $id,
        TenantId $tenantId,
        ContractId $contractId,
        YearMonth $month,
        Money $amount,
        ?DateTimeImmutable $paidOn,
    ): self {
        return new self($id, $tenantId, $contractId, $month, $amount, $paidOn);
    }

    public function id(): FeeId
    {
        return $this->id ?? throw new LogicException('The fee has not been persisted yet.');
    }

    public function paidOn(): ?DateTimeImmutable
    {
        return $this->paidOn;
    }

    /**
     * @throws FeeAlreadyPaid
     */
    public function markPaid(DateTimeImmutable $on): void
    {
        if ($this->paidOn !== null) {
            throw FeeAlreadyPaid::withId($this->id());
        }

        $this->paidOn = $on;
    }

    public function markUnpaid(): void
    {
        $this->paidOn = null;
    }

    public function statusOn(DateTimeImmutable $day): FeeStatus
    {
        return match (true) {
            $this->paidOn !== null => FeeStatus::Paid,
            $this->month->days()->endString() < $day->format('Y-m-d') => FeeStatus::Overdue,
            default => FeeStatus::Pending,
        };
    }
}
