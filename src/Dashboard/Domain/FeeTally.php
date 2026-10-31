<?php

namespace GarAge\Dashboard\Domain;

use GarAge\Billing\Domain\Fee;
use GarAge\Shared\Domain\Money;

/**
 * Number of fees and what they add up to.
 */
final readonly class FeeTally
{
    private function __construct(
        public int $count,
        public Money $amount,
    ) {}

    /**
     * @param  list<Fee>  $fees
     */
    public static function of(array $fees): self
    {
        return new self(
            count($fees),
            array_reduce($fees, fn (Money $sum, Fee $fee): Money => $sum->plus($fee->amount), Money::fromHundredths(0)),
        );
    }
}
