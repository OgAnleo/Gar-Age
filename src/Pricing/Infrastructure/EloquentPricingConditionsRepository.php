<?php

namespace GarAge\Pricing\Infrastructure;

use App\Models\Tenant as TenantModel;
use GarAge\Pricing\Domain\DiscountPercent;
use GarAge\Pricing\Domain\PricingConditions;
use GarAge\Pricing\Domain\PricingConditionsRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Adapter: the pricing conditions are columns of the tenants table.
 */
final class EloquentPricingConditionsRepository implements PricingConditionsRepository
{
    public function forTenant(TenantId $tenantId): PricingConditions
    {
        $tenant = TenantModel::find($tenantId->value);

        return $tenant === null ? PricingConditions::none() : new PricingConditions(
            new DiscountPercent($tenant->weekly_discount_percent),
            new DiscountPercent($tenant->monthly_discount_percent),
            new DiscountPercent($tenant->annual_discount_percent),
        );
    }

    public function save(TenantId $tenantId, PricingConditions $conditions): void
    {
        TenantModel::whereKey($tenantId->value)->update([
            'weekly_discount_percent' => $conditions->weekly->value,
            'monthly_discount_percent' => $conditions->monthly->value,
            'annual_discount_percent' => $conditions->annual->value,
        ]);
    }
}
