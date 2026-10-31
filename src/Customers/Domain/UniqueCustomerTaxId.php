<?php

namespace GarAge\Customers\Domain;

use GarAge\Tenancy\Domain\TenantId;

/**
 * Domain service: a tax id can only be used once per tenant.
 */
final readonly class UniqueCustomerTaxId
{
    public function __construct(private CustomerRepository $customers) {}

    /**
     * @throws CustomerTaxIdAlreadyUsed
     */
    public function ensureAvailable(TenantId $tenantId, TaxId $taxId, ?CustomerId $except = null): void
    {
        if ($this->customers->taxIdTaken($tenantId, $taxId, $except)) {
            throw CustomerTaxIdAlreadyUsed::taxId($taxId);
        }
    }
}
