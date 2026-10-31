<?php

namespace GarAge\Catalog\Domain;

use GarAge\Customers\Domain\Email;
use GarAge\Customers\Domain\PhoneNumber;

/**
 * Whether a business publishes its free units, and how visitors can contact it.
 */
final readonly class PublicPage
{
    public function __construct(
        public bool $enabled,
        public ?PhoneNumber $phone = null,
        public ?Email $email = null,
    ) {}

    public static function unpublished(): self
    {
        return new self(false);
    }
}
