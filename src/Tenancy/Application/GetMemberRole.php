<?php

namespace GarAge\Tenancy\Application;

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Domain\Role;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Tenancy\Domain\TenantRepository;

/**
 * Use case: the user's role in the business (null when not a member), which
 * decides what they may do there.
 */
final readonly class GetMemberRole
{
    public function __construct(private TenantRepository $tenants) {}

    public function handle(TenantId $tenantId, UserId $userId): ?Role
    {
        return $this->tenants->roleOf($tenantId, $userId);
    }
}
