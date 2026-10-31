<?php

namespace GarAge\Tenancy\Domain;

use GarAge\Shared\Domain\UserId;

/**
 * A user account just created for someone, with the temporary password to
 * hand over to them (it is not stored anywhere in clear).
 */
final readonly class NewAccount
{
    public function __construct(
        public UserId $userId,
        public string $temporaryPassword,
    ) {}
}
