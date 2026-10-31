<?php

namespace GarAge\Tenancy\Application;

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Domain\NotTenantOwner;
use GarAge\Tenancy\Domain\Role;
use GarAge\Tenancy\Domain\TenantEraser;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Tenancy\Domain\TenantRepository;

/**
 * Use case: an owner deletes their business with all of its data.
 */
final readonly class DeleteBusiness
{
    public function __construct(
        private TenantRepository $tenants,
        private TenantEraser $eraser,
    ) {}

    /**
     * @throws NotTenantOwner
     */
    public function handle(TenantId $tenantId, UserId $userId): void
    {
        if ($this->tenants->roleOf($tenantId, $userId) !== Role::Owner) {
            throw NotTenantOwner::of($tenantId, $userId);
        }

        $this->eraser->erase($tenantId);
    }
}
