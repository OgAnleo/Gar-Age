<?php

namespace GarAge\Units\Domain;

use GarAge\Shared\Domain\StillInUse;

final class UnitInUse extends StillInUse
{
    public static function withId(UnitId $id): self
    {
        return new self("Unit {$id->value} has contracts and cannot be deleted.");
    }
}
