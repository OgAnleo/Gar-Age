<?php

namespace GarAge\Pricing\Application;

use GarAge\Pricing\Domain\DiscountPercent;
use GarAge\Pricing\Domain\PricingConditions;
use GarAge\Pricing\Domain\PricingConditionsRepository;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: the owner changes the discounts. Existing contracts keep the price
 * they were signed with; only new bookings use the new conditions.
 */
final readonly class UpdatePricingConditions
{
    public function __construct(private PricingConditionsRepository $conditions) {}

    /**
     * @throws InvalidValue
     */
    public function handle(TenantId $tenantId, int $weekly, int $monthly, int $annual): void
    {
        $this->conditions->save($tenantId, new PricingConditions(
            new DiscountPercent($weekly),
            new DiscountPercent($monthly),
            new DiscountPercent($annual),
        ));
    }
}
