<?php

namespace GarAge\Billing\Domain;

use GarAge\Shared\Domain\DomainError;

final class FeeAlreadyPaid extends DomainError
{
    public static function withId(FeeId $id): self
    {
        return new self("Fee {$id->value} is already paid.");
    }
}
