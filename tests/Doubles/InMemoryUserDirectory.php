<?php

namespace Tests\Doubles;

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Domain\NewAccount;
use GarAge\Tenancy\Domain\UserDirectory;

/**
 * Test adapter: UserDirectory port kept in memory (no database).
 */
final class InMemoryUserDirectory implements UserDirectory
{
    /** @var array<string, int> email => user id */
    private array $users = [];

    private int $nextId = 100;

    public function add(string $email): UserId
    {
        $this->users[$email] = $this->nextId++;

        return new UserId($this->users[$email]);
    }

    public function findByEmail(string $email): ?UserId
    {
        return isset($this->users[$email]) ? new UserId($this->users[$email]) : null;
    }

    public function registerWithTemporaryPassword(string $name, string $email): NewAccount
    {
        return new NewAccount($this->add($email), "temporary-{$email}");
    }
}
