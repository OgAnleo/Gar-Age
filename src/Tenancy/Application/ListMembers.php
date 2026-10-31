<?php

namespace GarAge\Tenancy\Application;

use GarAge\Tenancy\Domain\Member;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Tenancy\Domain\TenantRepository;

/**
 * Use case: the people of a business, owners first.
 */
final readonly class ListMembers
{
    public function __construct(private TenantRepository $tenants) {}

    /**
     * @return list<Member>
     */
    public function handle(TenantId $tenantId): array
    {
        return $this->tenants->members($tenantId);
    }
}
