<?php

namespace GarAge\Customers\Domain;

/**
 * Everything that describes a customer, validated by its value objects.
 */
final readonly class CustomerDetails
{
    public function __construct(
        public CustomerName $name,
        public TaxId $taxId,
        public ?Email $email = null,
        public ?PhoneNumber $phone = null,
    ) {}
}
