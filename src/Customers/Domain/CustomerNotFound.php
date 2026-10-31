<?php

namespace GarAge\Customers\Domain;

use GarAge\Shared\Domain\NotFound;

final class CustomerNotFound extends NotFound
{
    public static function withId(CustomerId $id): self
    {
        return new self("No customer with id {$id->value} in this tenant.");
    }
}
