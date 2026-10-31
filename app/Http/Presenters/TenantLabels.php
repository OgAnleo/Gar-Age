<?php

namespace App\Http\Presenters;

use GarAge\Customers\Domain\Customer;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitId;

/**
 * Human labels of a tenant's units and customers, so presenters can show
 * "G-01" or "Ana López" instead of ids.
 */
final readonly class TenantLabels
{
    /**
     * @param  array<int, string>  $unitCodes
     * @param  array<int, string>  $customerNames
     */
    private function __construct(
        private array $unitCodes,
        private array $customerNames,
    ) {}

    /**
     * @param  list<Unit>  $units
     * @param  list<Customer>  $customers
     */
    public static function from(array $units, array $customers): self
    {
        $unitCodes = [];
        foreach ($units as $unit) {
            $unitCodes[$unit->id()->value] = $unit->details()->code->value;
        }

        $customerNames = [];
        foreach ($customers as $customer) {
            $customerNames[$customer->id()->value] = $customer->details()->name->value;
        }

        return new self($unitCodes, $customerNames);
    }

    public function unitCode(UnitId $id): string
    {
        return $this->unitCodes[$id->value] ?? '?';
    }

    public function customerName(CustomerId $id): string
    {
        return $this->customerNames[$id->value] ?? '?';
    }
}
