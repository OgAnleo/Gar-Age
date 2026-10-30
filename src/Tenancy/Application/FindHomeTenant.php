<?php

namespace GarAge\Tenancy\Application;

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Domain\Tenant;
use GarAge\Tenancy\Domain\TenantRepository;

/**
 * Use case: the tenant a user lands on after logging in.
 */
final readonly class FindHomeTenant
{
    public function __construct(private TenantRepository $tenants) {}

    public function handle(UserId $userId): ?Tenant
    {
        return $this->tenants->firstOfUser($userId);
    }
}
