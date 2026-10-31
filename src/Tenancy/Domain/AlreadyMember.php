<?php

namespace GarAge\Tenancy\Domain;

use GarAge\Shared\Domain\DomainError;
use GarAge\Shared\Domain\UserId;

/**
 * The user already belongs to the tenant.
 */
final class AlreadyMember extends DomainError
{
    public static function of(TenantId $tenantId, UserId $userId): self
    {
        return new self("User {$userId->value} already belongs to tenant {$tenantId->value}.");
    }
}
