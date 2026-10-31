<?php

namespace GarAge\Tenancy\Application;

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Domain\NotTenantOwner;
use GarAge\Tenancy\Domain\Role;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Tenancy\Domain\TenantRepository;

/**
 * Guard shared by the use cases only an owner may run.
 */
final readonly class RequireOwner
{
    public function __construct(private TenantRepository $tenants) {}

    /**
     * @throws NotTenantOwner
     */
    public function check(TenantId $tenantId, UserId $userId): void
    {
        if ($this->tenants->roleOf($tenantId, $userId) !== Role::Owner) {
            throw NotTenantOwner::of($tenantId, $userId);
        }
    }
}
