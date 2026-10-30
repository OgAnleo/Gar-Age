<?php

namespace App\Tenancy;

use GarAge\Tenancy\Domain\Tenant;
use LogicException;

/**
 * The tenant of the current request (set by the IdentifyTenant middleware).
 */
class CurrentTenant
{
    private ?Tenant $tenant = null;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id->value;
    }

    /**
     * The current tenant, for code that only runs inside tenant routes.
     */
    public function require(): Tenant
    {
        return $this->tenant ?? throw new LogicException('No current tenant: the route is missing the tenant middleware.');
    }
}
