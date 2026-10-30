<?php

namespace GarAge\Tenancy\Application;

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Tenancy\Domain\TenantRepository;

/**
 * Use case: does the user belong to the tenant?
 */
final readonly class CheckMembership
{
    public function __construct(private TenantRepository $tenants) {}

    public function handle(TenantId $tenantId, UserId $userId): bool
    {
        return $this->tenants->isMember($tenantId, $userId);
    }
}
