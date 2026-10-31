<?php

namespace GarAge\Contracts\Domain;

use GarAge\Shared\Domain\NotFound;

final class ContractNotFound extends NotFound
{
    public static function withId(ContractId $id): self
    {
        return new self("No contract with id {$id->value} in this tenant.");
    }
}
