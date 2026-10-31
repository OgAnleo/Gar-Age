<?php

namespace GarAge\Tenancy\Domain;

use GarAge\Shared\Domain\UserId;

/**
 * A user who belongs to a tenant, with their role in it.
 */
final readonly class Member
{
    public function __construct(
        public UserId $userId,
        public string $name,
        public string $email,
        public Role $role,
    ) {}
}
