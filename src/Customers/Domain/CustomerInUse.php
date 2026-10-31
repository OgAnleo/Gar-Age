<?php

namespace GarAge\Customers\Domain;

use GarAge\Shared\Domain\StillInUse;

final class CustomerInUse extends StillInUse
{
    public static function withId(CustomerId $id): self
    {
        return new self("Customer {$id->value} has contracts and cannot be deleted.");
    }
}
