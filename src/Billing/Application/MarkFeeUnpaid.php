<?php

namespace GarAge\Billing\Application;

use GarAge\Billing\Domain\FeeId;
use GarAge\Billing\Domain\FeeNotFound;
use GarAge\Billing\Domain\FeeRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: undo a payment recorded by mistake.
 */
final readonly class MarkFeeUnpaid
{
    public function __construct(
        private GetFee $getFee,
        private FeeRepository $fees,
    ) {}

    /**
     * @throws FeeNotFound
     */
    public function handle(TenantId $tenantId, FeeId $feeId): void
    {
        $fee = $this->getFee->handle($tenantId, $feeId);
        $fee->markUnpaid();
        $this->fees->save($fee);
    }
}
