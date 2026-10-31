<?php

namespace GarAge\Tenancy\Application;

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Domain\Tenant;
use GarAge\Tenancy\Domain\TenantRepository;

/**
 * Use case: the businesses a user owns (an owner must delete them before
 * deleting their account, so no business is left without an owner).
 */
final readonly class ListOwnedBusinesses
{
    public function __construct(private TenantRepository $tenants) {}

    /**
     * @return list<Tenant>
     */
    public function handle(UserId $userId): array
    {
        return $this->tenants->ownedBy($userId);
    }
}
