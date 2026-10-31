<?php

namespace GarAge\Contracts\Application;

use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\ContractNotFound;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: one contract of a tenant. Contracts of other tenants are "not found".
 */
final readonly class GetContract
{
    public function __construct(private ContractRepository $contracts) {}

    /**
     * @throws ContractNotFound
     */
    public function handle(TenantId $tenantId, ContractId $contractId): Contract
    {
        return $this->contracts->find($tenantId, $contractId) ?? throw ContractNotFound::withId($contractId);
    }
}
