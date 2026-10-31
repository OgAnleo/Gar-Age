<?php

namespace GarAge\Customers\Application;

use GarAge\Customers\Domain\Customer;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Customers\Domain\CustomerNotFound;
use GarAge\Customers\Domain\CustomerRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: one customer of a tenant. Customers of other tenants are "not found".
 */
final readonly class GetCustomer
{
    public function __construct(private CustomerRepository $customers) {}

    /**
     * @throws CustomerNotFound
     */
    public function handle(TenantId $tenantId, CustomerId $customerId): Customer
    {
        return $this->customers->find($tenantId, $customerId) ?? throw CustomerNotFound::withId($customerId);
    }
}
