<?php

namespace GarAge\Contracts\Application;

use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\ContractNotFound;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Contracts\Domain\UnitAlreadyRented;
use GarAge\Customers\Domain\CustomerNotFound;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitNotFound;

/**
 * Use case: change a contract, keeping its unit free of overlapping contracts.
 */
final readonly class UpdateContract
{
    public function __construct(
        private GetContract $getContract,
        private ContractRepository $contracts,
        private ContractRules $rules,
    ) {}

    /**
     * @throws ContractNotFound
     * @throws UnitNotFound
     * @throws CustomerNotFound
     * @throws UnitAlreadyRented
     */
    public function handle(TenantId $tenantId, ContractId $contractId, ContractInput $input): void
    {
        $contract = $this->getContract->handle($tenantId, $contractId);
        $details = $input->toDetails();

        $this->rules->check($tenantId, $details, $contractId);

        $contract->changeDetails($details);
        $this->contracts->save($contract);
    }
}
