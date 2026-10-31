<?php

namespace GarAge\Customers\Domain;

use GarAge\Shared\Domain\BoundedText;

/**
 * Full name of a person or company name.
 */
final readonly class CustomerName extends BoundedText
{
    public const int MAX_LENGTH = 120;

    protected const string DESCRIPTION = 'A customer name';
}
