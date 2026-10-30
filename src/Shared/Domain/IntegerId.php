<?php

namespace GarAge\Shared\Domain;

/**
 * Identity backed by a positive integer (database auto-increment keys).
 */
abstract readonly class IntegerId
{
    final public function __construct(public int $value)
    {
        if ($value < 1) {
            throw new InvalidValue(sprintf('%s must be a positive integer, %d given.', static::class, $value));
        }
    }

    public function equals(self $other): bool
    {
        return $other::class === static::class && $other->value === $this->value;
    }
}
