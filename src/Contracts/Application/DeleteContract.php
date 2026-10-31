<?php

namespace GarAge\Contracts\Application;

use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\ContractInUse;
use GarAge\Contracts\Domain\ContractNotFound;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Contracts\Domain\ContractUsage;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: remove a contract of a tenant, unless something still refers to it.
 */
final readonly class DeleteContract
{
    public function __construct(
        private GetContract $getContract,
        private ContractRepository $contracts,
        private ContractUsage $usage,
    ) {}

    /**
     * @throws ContractNotFound
     * @throws ContractInUse
     */
    public function handle(TenantId $tenantId, ContractId $contractId): void
    {
        $contract = $this->getContract->handle($tenantId, $contractId);

        if ($this->usage->isInUse($tenantId, $contractId)) {
            throw ContractInUse::withId($contractId);
        }

        $this->contracts->remove($contract);
    }
}
