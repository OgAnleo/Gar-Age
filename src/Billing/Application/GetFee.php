<?php

namespace GarAge\Billing\Application;

use GarAge\Billing\Domain\Fee;
use GarAge\Billing\Domain\FeeId;
use GarAge\Billing\Domain\FeeNotFound;
use GarAge\Billing\Domain\FeeRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: one fee of a tenant. Fees of other tenants are "not found".
 */
final readonly class GetFee
{
    public function __construct(private FeeRepository $fees) {}

    /**
     * @throws FeeNotFound
     */
    public function handle(TenantId $tenantId, FeeId $feeId): Fee
    {
        return $this->fees->find($tenantId, $feeId) ?? throw FeeNotFound::withId($feeId);
    }
}
