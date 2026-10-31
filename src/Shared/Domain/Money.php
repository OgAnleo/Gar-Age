<?php

namespace GarAge\Shared\Domain;

/**
 * Amount in euros kept as integer cents (79.99 € = 7999).
 */
final readonly class Money extends TwoDecimalNumber
{
    private const int MAX_CENTS = 999_999_999;

    public function cents(): int
    {
        return $this->hundredths;
    }

    public function times(int $factor): self
    {
        return self::fromHundredths($this->hundredths * $factor);
    }

    /**
     * A percentage of this amount, rounded half up to the cent (60% of 79.99 € = 47.99 €).
     */
    public function percent(int $percent): self
    {
        return self::fromHundredths(intdiv($this->hundredths * $percent + 50, 100));
    }

    protected static function guard(int $hundredths): void
    {
        if ($hundredths < 0 || $hundredths > self::MAX_CENTS) {
            throw new InvalidValue('An amount must be between 0 and 9999999.99 €.');
        }
    }
}
