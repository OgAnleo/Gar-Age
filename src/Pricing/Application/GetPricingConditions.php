<?php

namespace GarAge\Pricing\Application;

use GarAge\Pricing\Domain\PricingConditions;
use GarAge\Pricing\Domain\PricingConditionsRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: the pricing conditions of a business.
 */
final readonly class GetPricingConditions
{
    public function __construct(private PricingConditionsRepository $conditions) {}

    public function handle(TenantId $tenantId): PricingConditions
    {
        return $this->conditions->forTenant($tenantId);
    }
}
