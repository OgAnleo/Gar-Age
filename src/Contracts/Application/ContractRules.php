<?php

namespace GarAge\Contracts\Application;

use GarAge\Contracts\Domain\ContractDetails;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\UnitAlreadyRented;
use GarAge\Contracts\Domain\UnitAvailability;
use GarAge\Customers\Domain\CustomerNotFound;
use GarAge\Customers\Domain\CustomerRepository;
use GarAge\Pricing\Domain\PricingConditionsRepository;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitNotFound;
use GarAge\Units\Domain\UnitRepository;

/**
 * Shared by creating and updating a contract: checks that the unit and the
 * customer belong to the tenant and that the unit is free, then prices the
 * contract with the unit's daily rate and the business's current conditions.
 */
final readonly class ContractRules
{
    public function __construct(
        private UnitRepository $units,
        private CustomerRepository $customers,
        private UnitAvailability $availability,
        private PricingConditionsRepository $pricing,
    ) {}

    /**
     * @throws InvalidValue
     * @throws UnitNotFound
     * @throws CustomerNotFound
     * @throws UnitAlreadyRented
     */
    public function detailsFor(TenantId $tenantId, ContractInput $input, ?ContractId $except = null): ContractDetails
    {
        $unitId = $input->unitId();
        $customerId = $input->customerId();
        $schedule = $input->schedule();

        $unit = $this->units->find($tenantId, $unitId) ?? throw UnitNotFound::withId($unitId);
        $this->customers->find($tenantId, $customerId) ?? throw CustomerNotFound::withId($customerId);
        $this->availability->ensureFree($tenantId, $unitId, $schedule, $except);

        $price = $schedule->priceWith($this->pricing->forTenant($tenantId), $unit->details()->dailyPrice);

        return new ContractDetails($unitId, $customerId, $schedule, $price);
    }
}
