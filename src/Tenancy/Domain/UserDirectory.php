<?php

namespace GarAge\Tenancy\Domain;

use GarAge\Shared\Domain\UserId;

/**
 * Port: the user accounts of the application (owned by the framework's
 * authentication), as far as tenants need them to add members.
 */
interface UserDirectory
{
    public function findByEmail(string $email): ?UserId;

    /**
     * Create a verified account with a random temporary password.
     */
    public function registerWithTemporaryPassword(string $name, string $email): NewAccount;
}
