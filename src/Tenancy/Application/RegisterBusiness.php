<?php

namespace GarAge\Tenancy\Application;

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Domain\Role;
use GarAge\Tenancy\Domain\Slugger;
use GarAge\Tenancy\Domain\Tenant;
use GarAge\Tenancy\Domain\TenantRepository;
use GarAge\Tenancy\Domain\TenantSlug;

/**
 * Use case: create a business with a unique slug, owned by the given user.
 */
final readonly class RegisterBusiness
{
    public function __construct(
        private TenantRepository $tenants,
        private Slugger $slugger,
    ) {}

    public function handle(string $name, UserId $owner): Tenant
    {
        $tenant = $this->tenants->create($name, $this->availableSlug($name));

        $this->tenants->addMember($tenant->id, $owner, Role::Owner);

        return $tenant;
    }

    private function availableSlug(string $name): TenantSlug
    {
        $base = TenantSlug::fromSlugified($this->slugger->slugify($name));
        $slug = $base;
        $suffix = 2;

        while ($slug->isReserved() || $this->tenants->slugExists($slug)) {
            $slug = $base->withSuffix($suffix++);
        }

        return $slug;
    }
}
