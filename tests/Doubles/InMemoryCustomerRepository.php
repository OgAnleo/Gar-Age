<?php

namespace Tests\Doubles;

use GarAge\Customers\Domain\Customer;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Customers\Domain\CustomerRepository;
use GarAge\Customers\Domain\TaxId;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Test adapter: CustomerRepository port kept in memory (no database).
 */
final class InMemoryCustomerRepository implements CustomerRepository
{
    /** @var array<int, Customer> */
    private array $customers = [];

    private int $nextId = 1;

    public function add(Customer $customer): CustomerId
    {
        $id = new CustomerId($this->nextId++);
        $this->customers[$id->value] = Customer::restore($id, $customer->tenantId, $customer->details());

        return $id;
    }

    public function save(Customer $customer): void
    {
        $this->customers[$customer->id()->value] = $customer;
    }

    public function remove(Customer $customer): void
    {
        unset($this->customers[$customer->id()->value]);
    }

    public function find(TenantId $tenantId, CustomerId $customerId): ?Customer
    {
        $customer = $this->customers[$customerId->value] ?? null;

        return $customer !== null && $customer->tenantId->equals($tenantId) ? $customer : null;
    }

    public function allOfTenant(TenantId $tenantId): array
    {
        $customers = array_values(array_filter(
            $this->customers,
            fn (Customer $customer): bool => $customer->tenantId->equals($tenantId),
        ));

        usort($customers, fn (Customer $a, Customer $b): int => strcmp($a->details()->name->value, $b->details()->name->value));

        return $customers;
    }

    public function taxIdTaken(TenantId $tenantId, TaxId $taxId, ?CustomerId $except = null): bool
    {
        foreach ($this->allOfTenant($tenantId) as $customer) {
            if ($customer->details()->taxId->equals($taxId) && ($except === null || ! $customer->id()->equals($except))) {
                return true;
            }
        }

        return false;
    }
}
