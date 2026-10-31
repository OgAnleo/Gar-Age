<?php

namespace Tests\Doubles;

use GarAge\Pricing\Domain\PricingConditions;
use GarAge\Pricing\Domain\PricingConditionsRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Test adapter: PricingConditionsRepository port kept in memory (no database).
 */
final class InMemoryPricingConditionsRepository implements PricingConditionsRepository
{
    /** @var array<int, PricingConditions> */
    private array $conditions = [];

    public function forTenant(TenantId $tenantId): PricingConditions
    {
        return $this->conditions[$tenantId->value] ?? PricingConditions::none();
    }

    public function save(TenantId $tenantId, PricingConditions $conditions): void
    {
        $this->conditions[$tenantId->value] = $conditions;
    }
}
