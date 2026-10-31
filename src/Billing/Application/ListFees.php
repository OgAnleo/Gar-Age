<?php

namespace GarAge\Billing\Application;

use GarAge\Billing\Domain\Fee;
use GarAge\Billing\Domain\FeeRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: the fees of a tenant, newest month first.
 */
final readonly class ListFees
{
    public function __construct(private FeeRepository $fees) {}

    /**
     * @return list<Fee>
     */
    public function handle(TenantId $tenantId): array
    {
        return $this->fees->allOfTenant($tenantId);
    }
}
