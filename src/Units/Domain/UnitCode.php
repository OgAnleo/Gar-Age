<?php

namespace GarAge\Units\Domain;

use GarAge\Shared\Domain\BoundedText;

/**
 * Human label of a unit inside its business ("G-01"), unique per tenant.
 */
final readonly class UnitCode extends BoundedText
{
    public const int MAX_LENGTH = 20;

    protected const string DESCRIPTION = 'A unit code';

    public function equals(self $other): bool
    {
        return $other->value === $this->value;
    }
}
