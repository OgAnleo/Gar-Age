<?php

namespace GarAge\Customers\Application;

use GarAge\Customers\Domain\Customer;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Customers\Domain\CustomerRepository;
use GarAge\Customers\Domain\CustomerTaxIdAlreadyUsed;
use GarAge\Customers\Domain\UniqueCustomerTaxId;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: add a customer to a tenant.
 */
final readonly class CreateCustomer
{
    public function __construct(
        private CustomerRepository $customers,
        private UniqueCustomerTaxId $uniqueTaxId,
    ) {}

    /**
     * @throws CustomerTaxIdAlreadyUsed
     */
    public function handle(TenantId $tenantId, CustomerInput $input): CustomerId
    {
        $details = $input->toDetails();

        $this->uniqueTaxId->ensureAvailable($tenantId, $details->taxId);

        return $this->customers->add(Customer::create($tenantId, $details));
    }
}
