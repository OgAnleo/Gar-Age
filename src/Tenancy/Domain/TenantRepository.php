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
     * The user's role in the tenant, or null when not a member.
     */
    public function roleOf(TenantId $tenantId, UserId $userId): ?Role;

    /**
     * @return list<Tenant> The tenants the user owns, oldest first.
     */
    public function ownedBy(UserId $userId): array;

    /**
     * @return list<Member> Owners first, then by name.
     */
    public function members(TenantId $tenantId): array;

    public function removeMember(TenantId $tenantId, UserId $userId): void;

    /**
     * The first tenant the user joined, if any.
     */
    public function firstOfUser(UserId $userId): ?Tenant;
}
