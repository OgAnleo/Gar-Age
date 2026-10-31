<?php

namespace GarAge\Contracts\Application;

use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: the contracts of a tenant, newest start date first.
 */
final readonly class ListContracts
{
    public function __construct(private ContractRepository $contracts) {}

    /**
     * @return list<Contract>
     */
    public function handle(TenantId $tenantId): array
    {
        return $this->contracts->allOfTenant($tenantId);
    }
}
