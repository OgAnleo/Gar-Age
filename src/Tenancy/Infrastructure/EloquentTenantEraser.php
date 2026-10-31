<?php

namespace GarAge\Tenancy\Infrastructure;

use GarAge\Tenancy\Domain\TenantEraser;
use GarAge\Tenancy\Domain\TenantId;
use Illuminate\Support\Facades\DB;

/**
 * Adapter: deletes the tenant's rows child-first in one transaction. A plain
 * cascade from tenants is not enough: contracts and fees hold restrictOnDelete
 * keys to units, customers and contracts, which would refuse it.
 */
final class EloquentTenantEraser implements TenantEraser
{
    /** Tables with a tenant_id, children before the rows they refer to. */
    private const array TABLES = ['fees', 'contracts', 'customers', 'units'];

    public function erase(TenantId $tenantId): void
    {
        DB::transaction(function () use ($tenantId): void {
            foreach (self::TABLES as $table) {
                DB::table($table)->where('tenant_id', $tenantId->value)->delete();
            }

            // Memberships (tenant_user) and contract days go with their parents by cascade.
            DB::table('tenants')->where('id', $tenantId->value)->delete();
        });
    }
}
