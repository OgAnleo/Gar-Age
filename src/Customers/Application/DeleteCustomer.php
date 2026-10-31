<?php

namespace GarAge\Customers\Application;

use GarAge\Customers\Domain\CustomerId;
use GarAge\Customers\Domain\CustomerInUse;
use GarAge\Customers\Domain\CustomerNotFound;
use GarAge\Customers\Domain\CustomerRepository;
use GarAge\Customers\Domain\CustomerUsage;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: remove a customer of a tenant, unless something still refers to it.
 */
final readonly class DeleteCustomer
{
    public function __construct(
        private GetCustomer $getCustomer,
        private CustomerRepository $customers,
        private CustomerUsage $usage,
    ) {}

    /**
     * @throws CustomerNotFound
     * @throws CustomerInUse
     */
    public function handle(TenantId $tenantId, CustomerId $customerId): void
    {
        $customer = $this->getCustomer->handle($tenantId, $customerId);

        if ($this->usage->isInUse($tenantId, $customerId)) {
            throw CustomerInUse::withId($customerId);
        }

        $this->customers->remove($customer);
    }
}
