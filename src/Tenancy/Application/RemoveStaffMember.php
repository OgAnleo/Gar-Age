<?php

namespace GarAge\Tenancy\Application;

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Domain\NotStaffMember;
use GarAge\Tenancy\Domain\NotTenantOwner;
use GarAge\Tenancy\Domain\Role;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Tenancy\Domain\TenantRepository;

/**
 * Use case: an owner takes an employee out of the business (their account stays).
 */
final readonly class RemoveStaffMember
{
    public function __construct(
        private RequireOwner $requireOwner,
        private TenantRepository $tenants,
    ) {}

    /**
     * @throws NotTenantOwner
     * @throws NotStaffMember
     */
    public function handle(TenantId $tenantId, UserId $ownerId, UserId $staffId): void
    {
        $this->requireOwner->check($tenantId, $ownerId);

        if ($this->tenants->roleOf($tenantId, $staffId) !== Role::Staff) {
            throw NotStaffMember::of($tenantId, $staffId);
        }

        $this->tenants->removeMember($tenantId, $staffId);
    }
}
