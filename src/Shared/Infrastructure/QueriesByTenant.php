<?php

namespace GarAge\Shared\Infrastructure;

use App\Tenancy\TenantScope;
use GarAge\Shared\Domain\IntegerId;
use GarAge\Tenancy\Domain\TenantId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Queries for Eloquent repositories, scoped by tenant explicitly instead of
 * relying on the global scope (which depends on the current request).
 *
 * @template TModel of Model
 */
trait QueriesByTenant
{
    /**
     * @return class-string<TModel>
     */
    abstract protected function modelClass(): string;

    /**
     * @return Builder<TModel>
     */
    private function ofTenant(TenantId $tenantId): Builder
    {
        return $this->allTenants()->where('tenant_id', $tenantId->value);
    }

    /**
     * Rows of every tenant: only for jobs that work across tenants.
     *
     * @return Builder<TModel>
     */
    private function allTenants(): Builder
    {
        return $this->modelClass()::withoutGlobalScope(TenantScope::class);
    }

    /**
     * Whether the query matches a row other than $except.
     *
     * @param  Builder<TModel>  $query
     */
    private function existsExcept(Builder $query, ?IntegerId $except): bool
    {
        return $query->when($except !== null, fn (Builder $query) => $query->whereKeyNot($except?->value))->exists();
    }
}
