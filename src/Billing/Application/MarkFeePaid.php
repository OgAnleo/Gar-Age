<?php

namespace GarAge\Billing\Application;

use DateTimeImmutable;
use GarAge\Billing\Domain\FeeAlreadyPaid;
use GarAge\Billing\Domain\FeeId;
use GarAge\Billing\Domain\FeeNotFound;
use GarAge\Billing\Domain\FeeRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: record that a fee was paid on a given day.
 */
final readonly class MarkFeePaid
{
    public function __construct(
        private GetFee $getFee,
        private FeeRepository $fees,
    ) {}

    /**
     * @throws FeeNotFound
     * @throws FeeAlreadyPaid
     */
    public function handle(TenantId $tenantId, FeeId $feeId, DateTimeImmutable $paidOn): void
    {
        $fee = $this->getFee->handle($tenantId, $feeId);
        $fee->markPaid($paidOn);
        $this->fees->save($fee);
    }
}
