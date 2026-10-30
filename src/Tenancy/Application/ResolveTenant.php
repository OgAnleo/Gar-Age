<?php

namespace GarAge\Tenancy\Application;

use GarAge\Shared\Domain\InvalidValue;
use GarAge\Tenancy\Domain\Tenant;
use GarAge\Tenancy\Domain\TenantNotFound;
use GarAge\Tenancy\Domain\TenantRepository;
use GarAge\Tenancy\Domain\TenantSlug;

/**
 * Use case: find the tenant a URL slug points to.
 */
final readonly class ResolveTenant
{
    public function __construct(private TenantRepository $tenants) {}

    /**
     * @throws TenantNotFound
     */
    public function handle(string $slug): Tenant
    {
        try {
            $tenant = $this->tenants->findBySlug(new TenantSlug($slug));
        } catch (InvalidValue) {
            $tenant = null;
        }

        return $tenant ?? throw TenantNotFound::withSlug($slug);
    }
}
