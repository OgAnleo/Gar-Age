<?php

namespace GarAge\Contracts\Application;

use GarAge\Contracts\Domain\ContractDetails;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\UnitAlreadyRented;
use GarAge\Contracts\Domain\UnitAvailability;
use GarAge\Customers\Domain\CustomerNotFound;
use GarAge\Customers\Domain\CustomerRepository;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitNotFound;
use GarAge\Units\Domain\UnitRepository;

/**
 * Checks shared by creating and updating a contract: the unit and the customer
 * belong to the tenant, and the unit is free during the period.
 */
final readonly class ContractRules
{
    public function __construct(
        private UnitRepository $units,
        private CustomerRepository $customers,
        private UnitAvailability $availability,
    ) {}

    /**
     * @throws UnitNotFound
     * @throws CustomerNotFound
     * @throws UnitAlreadyRented
     */
    public function check(TenantId $tenantId, ContractDetails $details, ?ContractId $except = null): void
    {
        $this->units->find($tenantId, $details->unitId) ?? throw UnitNotFound::withId($details->unitId);
        $this->customers->find($tenantId, $details->customerId) ?? throw CustomerNotFound::withId($details->customerId);

        $this->availability->ensureFree($tenantId, $details, $except);
    }
}
