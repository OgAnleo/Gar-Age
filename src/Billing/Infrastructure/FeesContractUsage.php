<?php

namespace GarAge\Billing\Infrastructure;

use GarAge\Billing\Domain\FeeRepository;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\ContractUsage;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Adapter: a contract is in use while it has fees (their history must be kept).
 */
final readonly class FeesContractUsage implements ContractUsage
{
    public function __construct(private FeeRepository $fees) {}

    public function isInUse(TenantId $tenantId, ContractId $contractId): bool
    {
        return $this->fees->existsForContract($tenantId, $contractId);
    }
}
