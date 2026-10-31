<?php

namespace GarAge\Billing\Domain;

use DateTimeImmutable;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;
use LogicException;

/**
 * What a contract owes for one billing period (a week, a month, a year, or a
 * booking of specific days), and whether it has been paid. It is due when its
 * period starts and becomes overdue after a grace period without payment.
 */
final class Fee
{
    public const int GRACE_DAYS = 7;

    private function __construct(
        private readonly ?FeeId $id,
        public readonly TenantId $tenantId,
        public readonly ContractId $contractId,
        public readonly DateRange $period,
        public readonly Money $amount,
        private ?DateTimeImmutable $paidOn,
    ) {}

    /**
     * A new unpaid fee, not persisted yet (the repository assigns its id).
     */
    public static function issue(TenantId $tenantId, ContractId $contractId, DateRange $period, Money $amount): self
    {
        return new self(null, $tenantId, $contractId, $period, $amount, null);
    }

    /**
     * A fee rebuilt from storage.
     */
    public static function restore(
        FeeId $id,
        TenantId $tenantId,
        ContractId $contractId,
        DateRange $period,
        Money $amount,
        ?DateTimeImmutable $paidOn,
    ): self {
        return new self($id, $tenantId, $contractId, $period, $amount, $paidOn);
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
     * Last day to pay before the fee is overdue.
     */
    public function dueOn(): DateTimeImmutable
    {
        return $this->period->start->modify('+'.self::GRACE_DAYS.' days');
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
            $this->dueOn()->format('Y-m-d') < $day->format('Y-m-d') => FeeStatus::Overdue,
            default => FeeStatus::Pending,
        };
    }
}
