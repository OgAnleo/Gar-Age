<?php

namespace GarAge\Customers\Domain;

use GarAge\Tenancy\Domain\TenantId;

/**
 * Port: persistence of customers. Every read is scoped to one tenant.
 */
interface CustomerRepository
{
    public function add(Customer $customer): CustomerId;

    public function save(Customer $customer): void;

    public function remove(Customer $customer): void;

    public function find(TenantId $tenantId, CustomerId $customerId): ?Customer;

    /**
     * @return list<Customer> Ordered by name.
     */
    public function allOfTenant(TenantId $tenantId): array;

    public function taxIdTaken(TenantId $tenantId, TaxId $taxId, ?CustomerId $except = null): bool;
}
