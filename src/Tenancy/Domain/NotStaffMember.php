<?php

namespace GarAge\Tenancy\Domain;

use GarAge\Shared\Domain\NotFound;
use GarAge\Shared\Domain\UserId;

/**
 * Only staff can be removed from a tenant: owners stay, and strangers were never in it.
 */
final class NotStaffMember extends NotFound
{
    public static function of(TenantId $tenantId, UserId $userId): self
    {
        return new self("User {$userId->value} is not staff of tenant {$tenantId->value}.");
    }
}
