<?php

namespace GarAge\Contracts\Application;

use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Contracts\Domain\UnitAlreadyRented;
use GarAge\Customers\Domain\CustomerNotFound;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitNotFound;

/**
 * Use case: book a unit of the tenant for one of its customers, priced with
 * the business's current conditions.
 */
final readonly class CreateContract
{
    public function __construct(
        private ContractRepository $contracts,
        private ContractRules $rules,
    ) {}

    /**
     * @throws InvalidValue
     * @throws UnitNotFound
     * @throws CustomerNotFound
     * @throws UnitAlreadyRented
     */
    public function handle(TenantId $tenantId, ContractInput $input): ContractId
    {
        return $this->contracts->add(Contract::create($tenantId, $this->rules->detailsFor($tenantId, $input)));
    }
}
