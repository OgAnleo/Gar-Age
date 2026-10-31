<?php

namespace GarAge\Contracts\Domain;

use GarAge\Shared\Domain\StillInUse;

final class ContractInUse extends StillInUse
{
    public static function withId(ContractId $id): self
    {
        return new self("Contract {$id->value} has fees and cannot be deleted.");
    }
}
