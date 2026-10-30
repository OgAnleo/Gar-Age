<?php

namespace GarAge\Units\Domain;

use GarAge\Shared\Domain\InvalidValue;
use GarAge\Shared\Domain\TwoDecimalNumber;

/**
 * Floor area in square metres, between 0.01 and 9999.99 m².
 */
final readonly class Area extends TwoDecimalNumber
{
    private const int MAX_HUNDREDTHS = 999_999;

    protected static function guard(int $hundredths): void
    {
        if ($hundredths < 1 || $hundredths > self::MAX_HUNDREDTHS) {
            throw new InvalidValue('A unit area must be between 0.01 and 9999.99 m².');
        }
    }
}
