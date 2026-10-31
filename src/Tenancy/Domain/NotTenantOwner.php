<?php

namespace GarAge\Tenancy\Domain;

use GarAge\Shared\Domain\DomainError;
use GarAge\Shared\Domain\UserId;

/**
 * Only an owner may do this to the tenant (e.g. delete it).
 */
final class NotTenantOwner extends DomainError
{
    public static function of(TenantId $tenantId, UserId $userId): self
    {
        return new self("User {$userId->value} does not own tenant {$tenantId->value}.");
    }
}
