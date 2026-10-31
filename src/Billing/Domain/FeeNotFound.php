<?php

namespace GarAge\Billing\Domain;

use GarAge\Shared\Domain\NotFound;

final class FeeNotFound extends NotFound
{
    public static function withId(FeeId $id): self
    {
        return new self("No fee with id {$id->value} in this tenant.");
    }
}
