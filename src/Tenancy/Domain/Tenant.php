<?php

namespace GarAge\Tenancy\Domain;

use GarAge\Shared\Domain\InvalidValue;

/**
 * A business that rents storage rooms and garages.
 */
final readonly class Tenant
{
    public function __construct(
        public TenantId $id,
        public string $name,
        public TenantSlug $slug,
    ) {
        if (trim($name) === '') {
            throw new InvalidValue('A tenant needs a name.');
        }
    }
}
