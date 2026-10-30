<?php

namespace GarAge\Tenancy\Domain;

use GarAge\Shared\Domain\NotFound;

final class TenantNotFound extends NotFound
{
    public static function withSlug(string $slug): self
    {
        return new self("No tenant with slug \"{$slug}\".");
    }
}
