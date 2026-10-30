<?php

namespace GarAge\Shared\Domain;

/**
 * Exact decimal with two places, stored as an integer number of hundredths
 * so that no floating point rounding ever happens (79.99 € = 7999).
 */
abstract readonly class TwoDecimalNumber
{
    final protected function __construct(public int $hundredths)
    {
        static::guard($hundredths);
    }

    public static function fromHundredths(int $hundredths): static
    {
        return new static($hundredths);
    }

    /**
     * Parse "12", "12.5" or "12.50" (no sign, no exponent).
     */
    public static function fromString(string $value): static
    {
        if (preg_match('/^(\d{1,9})(?:\.(\d{1,2}))?$/', trim($value), $matches) !== 1) {
            throw new InvalidValue(sprintf('"%s" is not a number with up to two decimals.', $value));
        }

        return new static((int) $matches[1] * 100 + (int) str_pad($matches[2] ?? '0', 2, '0'));
    }

    public function toDecimalString(): string
    {
        return sprintf('%d.%02d', intdiv($this->hundredths, 100), $this->hundredths % 100);
    }

    /**
     * Throw InvalidValue when the amount is outside the allowed range.
     */
    abstract protected static function guard(int $hundredths): void;
}
