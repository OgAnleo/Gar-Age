<?php

namespace GarAge\Shared\Domain;

/**
 * Amount in euros kept as integer cents (79.99 € = 7999).
 */
final readonly class Money extends TwoDecimalNumber
{
    private const int MAX_CENTS = 9_999_999;

    public function cents(): int
    {
        return $this->hundredths;
    }

    protected static function guard(int $hundredths): void
    {
        if ($hundredths < 0 || $hundredths > self::MAX_CENTS) {
            throw new InvalidValue('An amount must be between 0 and 99999.99 €.');
        }
    }
}
