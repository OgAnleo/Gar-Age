<?php

namespace GarAge\Units\Domain;

use GarAge\Shared\Domain\DomainError;

final class UnitCodeAlreadyUsed extends DomainError
{
    public static function code(UnitCode $code): self
    {
        return new self("The code \"{$code->value}\" is already used by another unit of this tenant.");
    }
}
