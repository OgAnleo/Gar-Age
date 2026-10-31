<?php

namespace GarAge\Customers\Application;

use GarAge\Customers\Domain\CustomerDetails;
use GarAge\Customers\Domain\CustomerName;
use GarAge\Customers\Domain\Email;
use GarAge\Customers\Domain\PhoneNumber;
use GarAge\Customers\Domain\TaxId;
use GarAge\Shared\Domain\InvalidValue;

/**
 * Raw data for creating or updating a customer, as it arrives from outside.
 * Empty optional fields arrive as null.
 */
final readonly class CustomerInput
{
    public function __construct(
        public string $name,
        public string $taxId,
        public ?string $email = null,
        public ?string $phone = null,
    ) {}

    /**
     * @throws InvalidValue
     */
    public function toDetails(): CustomerDetails
    {
        return new CustomerDetails(
            new CustomerName($this->name),
            new TaxId($this->taxId),
            $this->email === null ? null : new Email($this->email),
            $this->phone === null ? null : new PhoneNumber($this->phone),
        );
    }
}
