<?php

namespace GarAge\Contracts\Infrastructure;

use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Customers\Domain\CustomerUsage;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Adapter: a customer is in use while they have contracts.
 */
final readonly class ContractsCustomerUsage implements CustomerUsage
{
    public function __construct(private ContractRepository $contracts) {}

    public function isInUse(TenantId $tenantId, CustomerId $customerId): bool
    {
        return $this->contracts->existsForCustomer($tenantId, $customerId);
    }
}
