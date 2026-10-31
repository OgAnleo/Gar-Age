<?php

namespace GarAge\Contracts\Application;

use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Contracts\Domain\UnitAlreadyRented;
use GarAge\Customers\Domain\CustomerNotFound;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitNotFound;

/**
 * Use case: rent a unit of the tenant to one of its customers.
 */
final readonly class CreateContract
{
    public function __construct(
        private ContractRepository $contracts,
        private ContractRules $rules,
    ) {}

    /**
     * @throws UnitNotFound
     * @throws CustomerNotFound
     * @throws UnitAlreadyRented
     */
    public function handle(TenantId $tenantId, ContractInput $input): ContractId
    {
        $details = $input->toDetails();

        $this->rules->check($tenantId, $details);

        return $this->contracts->add(Contract::create($tenantId, $details));
    }
}
