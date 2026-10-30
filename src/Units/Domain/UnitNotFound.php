<?php

namespace GarAge\Units\Domain;

use GarAge\Shared\Domain\NotFound;

final class UnitNotFound extends NotFound
{
    public static function withId(UnitId $id): self
    {
        return new self("No unit with id {$id->value} in this tenant.");
    }
}
