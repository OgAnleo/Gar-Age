<?php

namespace GarAge\Tenancy\Domain;

use GarAge\Shared\Domain\UserId;

/**
 * Port: persistence of tenants and their members.
 */
interface TenantRepository
{
    public function create(string $name, TenantSlug $slug): Tenant;

    public function findBySlug(TenantSlug $slug): ?Tenant;

    public function slugExists(TenantSlug $slug): bool;

    public function addMember(TenantId $tenantId, UserId $userId, Role $role): void;

    public function isMember(TenantId $tenantId, UserId $userId): bool;

    /**
     * The first tenant the user joined, if any.
     */
    public function firstOfUser(UserId $userId): ?Tenant;
}
