<?php

namespace GarAge\Catalog\Application;

use GarAge\Shared\Domain\Money;
use GarAge\Units\Domain\Unit;

/**
 * A free unit as visitors see it, with its price in each booking mode.
 */
final readonly class CatalogUnit
{
    /**
     * @param  array<string, Money>  $prices  Booking mode value => price (a day, or one period).
     */
    public function __construct(
        public Unit $unit,
        public array $prices,
    ) {}
}
