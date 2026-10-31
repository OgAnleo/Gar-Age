<?php

namespace GarAge\Contracts\Application;

use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\ContractNotFound;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: remove a contract of a tenant.
 */
final readonly class DeleteContract
{
    public function __construct(
        private GetContract $getContract,
        private ContractRepository $contracts,
    ) {}

    /**
     * @throws ContractNotFound
     */
    public function handle(TenantId $tenantId, ContractId $contractId): void
    {
        $this->contracts->remove($this->getContract->handle($tenantId, $contractId));
    }
}
