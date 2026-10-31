<?php

namespace GarAge\Customers\Domain;

use GarAge\Shared\Domain\DomainError;

final class CustomerTaxIdAlreadyUsed extends DomainError
{
    public static function taxId(TaxId $taxId): self
    {
        return new self("The tax id \"{$taxId->value}\" is already used by another customer of this tenant.");
    }
}
