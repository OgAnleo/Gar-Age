<?php

namespace GarAge\Pricing\Domain;

use GarAge\Shared\Domain\InvalidValue;

/**
 * Whole percentage taken off a price, from 0 to 100.
 */
final readonly class DiscountPercent
{
    public function __construct(public int $value)
    {
        if ($value < 0 || $value > 100) {
            throw new InvalidValue("A discount must be between 0 and 100%, {$value} given.");
        }
    }
}
