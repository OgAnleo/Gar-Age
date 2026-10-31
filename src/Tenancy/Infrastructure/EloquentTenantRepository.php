<?php

namespace GarAge\Tenancy\Infrastructure;

use App\Models\Tenant as TenantModel;
use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Domain\Role;
use GarAge\Tenancy\Domain\Tenant;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Tenancy\Domain\TenantRepository;
use GarAge\Tenancy\Domain\TenantSlug;
use Illuminate\Database\Eloquent\Builder;

/**
 * Adapter: TenantRepository port backed by Eloquent and the tenant_user pivot.
 */
final class EloquentTenantRepository implements TenantRepository
{
    public function create(string $name, TenantSlug $slug): Tenant
    {
        return TenantModel::create(['name' => $name, 'slug' => $slug->value])->toDomain();
    }

    public function findBySlug(TenantSlug $slug): ?Tenant
    {
        return TenantModel::where('slug', $slug->value)->first()?->toDomain();
    }

    public function slugExists(TenantSlug $slug): bool
    {
        return TenantModel::where('slug', $slug->value)->exists();
    }

    public function addMember(TenantId $tenantId, UserId $userId, Role $role): void
    {
        TenantModel::findOrFail($tenantId->value)->users()->attach($userId->value, ['role' => $role->value]);
    }

    public function isMember(TenantId $tenantId, UserId $userId): bool
    {
        return $this->ofUser($userId)->whereKey($tenantId->value)->exists();
    }

    public function roleOf(TenantId $tenantId, UserId $userId): ?Role
    {
        $role = TenantModel::findOrFail($tenantId->value)->users()->whereKey($userId->value)->first()?->pivot?->getAttribute('role');

        return is_string($role) ? Role::from($role) : null;
    }

    public function ownedBy(UserId $userId): array
    {
        return array_values(TenantModel::whereHas('users', fn (Builder $users) => $users
            ->whereKey($userId->value)
            ->where('tenant_user.role', Role::Owner->value))
            ->orderBy('id')
            ->get()
            ->map(fn (TenantModel $tenant): Tenant => $tenant->toDomain())
            ->all());
    }

    public function firstOfUser(UserId $userId): ?Tenant
    {
        return $this->ofUser($userId)->orderBy('id')->first()?->toDomain();
    }

    /**
     * @return Builder<TenantModel>
     */
    private function ofUser(UserId $userId): Builder
    {
        return TenantModel::whereHas('users', fn (Builder $users) => $users->whereKey($userId->value));
    }
}
