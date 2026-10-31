<?php

namespace Tests\Doubles;

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Domain\Role;
use GarAge\Tenancy\Domain\Tenant;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Tenancy\Domain\TenantRepository;
use GarAge\Tenancy\Domain\TenantSlug;

/**
 * Test adapter: TenantRepository port kept in memory (no database).
 */
final class InMemoryTenantRepository implements TenantRepository
{
    /** @var array<int, Tenant> */
    private array $tenants = [];

    /** @var array<int, array<int, Role>> tenant id => user id => role */
    private array $members = [];

    public function create(string $name, TenantSlug $slug): Tenant
    {
        $tenant = new Tenant(new TenantId(($this->tenants === [] ? 0 : max(array_keys($this->tenants))) + 1), $name, $slug);

        return $this->tenants[$tenant->id->value] = $tenant;
    }

    public function findBySlug(TenantSlug $slug): ?Tenant
    {
        foreach ($this->tenants as $tenant) {
            if ($tenant->slug->value === $slug->value) {
                return $tenant;
            }
        }

        return null;
    }

    public function slugExists(TenantSlug $slug): bool
    {
        return $this->findBySlug($slug) !== null;
    }

    public function addMember(TenantId $tenantId, UserId $userId, Role $role): void
    {
        $this->members[$tenantId->value][$userId->value] = $role;
    }

    public function isMember(TenantId $tenantId, UserId $userId): bool
    {
        return $this->roleOf($tenantId, $userId) !== null;
    }

    public function firstOfUser(UserId $userId): ?Tenant
    {
        foreach ($this->tenants as $tenant) {
            if ($this->isMember($tenant->id, $userId)) {
                return $tenant;
            }
        }

        return null;
    }

    public function ownedBy(UserId $userId): array
    {
        return array_values(array_filter(
            $this->tenants,
            fn (Tenant $tenant): bool => $this->roleOf($tenant->id, $userId) === Role::Owner,
        ));
    }

    /**
     * Forget a tenant and its members (what the TenantEraser double does).
     */
    public function remove(TenantId $tenantId): void
    {
        unset($this->tenants[$tenantId->value], $this->members[$tenantId->value]);
    }

    public function roleOf(TenantId $tenantId, UserId $userId): ?Role
    {
        return $this->members[$tenantId->value][$userId->value] ?? null;
    }
}
