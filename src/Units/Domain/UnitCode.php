<?php

namespace GarAge\Units\Domain;

use GarAge\Shared\Domain\InvalidValue;

/**
 * Human label of a unit inside its business ("G-01"), unique per tenant.
 */
final readonly class UnitCode
{
    public const int MAX_LENGTH = 20;

    public string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '' || mb_strlen($value) > self::MAX_LENGTH) {
            throw new InvalidValue(sprintf('A unit code needs 1 to %d characters.', self::MAX_LENGTH));
        }

        $this->value = $value;
    }

    public function equals(self $other): bool
    {
        return $other->value === $this->value;
    }
}
