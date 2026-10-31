<?php

namespace GarAge\Customers\Application;

use GarAge\Customers\Domain\CustomerId;
use GarAge\Customers\Domain\CustomerNotFound;
use GarAge\Customers\Domain\CustomerRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: remove a customer of a tenant.
 */
final readonly class DeleteCustomer
{
    public function __construct(
        private GetCustomer $getCustomer,
        private CustomerRepository $customers,
    ) {}

    /**
     * @throws CustomerNotFound
     */
    public function handle(TenantId $tenantId, CustomerId $customerId): void
    {
        $this->customers->remove($this->getCustomer->handle($tenantId, $customerId));
    }
}
