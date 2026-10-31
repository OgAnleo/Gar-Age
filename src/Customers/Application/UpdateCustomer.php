<?php

namespace GarAge\Customers\Application;

use GarAge\Customers\Domain\CustomerId;
use GarAge\Customers\Domain\CustomerNotFound;
use GarAge\Customers\Domain\CustomerRepository;
use GarAge\Customers\Domain\CustomerTaxIdAlreadyUsed;
use GarAge\Customers\Domain\UniqueCustomerTaxId;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: change the details of a customer, keeping its tax id unique.
 */
final readonly class UpdateCustomer
{
    public function __construct(
        private GetCustomer $getCustomer,
        private CustomerRepository $customers,
        private UniqueCustomerTaxId $uniqueTaxId,
    ) {}

    /**
     * @throws CustomerNotFound
     * @throws CustomerTaxIdAlreadyUsed
     */
    public function handle(TenantId $tenantId, CustomerId $customerId, CustomerInput $input): void
    {
        $customer = $this->getCustomer->handle($tenantId, $customerId);
        $details = $input->toDetails();

        $this->uniqueTaxId->ensureAvailable($tenantId, $details->taxId, $customerId);

        $customer->changeDetails($details);
        $this->customers->save($customer);
    }
}
