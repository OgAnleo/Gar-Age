<?php

namespace GarAge\Customers\Application;

use GarAge\Customers\Domain\Customer;
use GarAge\Customers\Domain\CustomerRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: the customers of a tenant, ordered by name.
 */
final readonly class ListCustomers
{
    public function __construct(private CustomerRepository $customers) {}

    /**
     * @return list<Customer>
     */
    public function handle(TenantId $tenantId): array
    {
        return $this->customers->allOfTenant($tenantId);
    }
}
