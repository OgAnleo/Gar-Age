<?php

namespace GarAge\Pricing\Domain;

use GarAge\Tenancy\Domain\TenantId;

/**
 * Port: the pricing conditions of each business.
 */
interface PricingConditionsRepository
{
    /**
     * Conditions of the tenant (no discounts until the owner sets them).
     */
    public function forTenant(TenantId $tenantId): PricingConditions;

    public function save(TenantId $tenantId, PricingConditions $conditions): void;
}
