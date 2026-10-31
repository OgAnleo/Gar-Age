<?php

namespace GarAge\Tenancy\Infrastructure;

use App\Models\User;
use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Domain\NewAccount;
use GarAge\Tenancy\Domain\UserDirectory;
use Illuminate\Support\Str;

/**
 * Adapter: UserDirectory port backed by the framework's users table.
 */
final class EloquentUserDirectory implements UserDirectory
{
    public function findByEmail(string $email): ?UserId
    {
        $id = User::where('email', $email)->value('id');

        return is_int($id) ? new UserId($id) : null;
    }

    public function registerWithTemporaryPassword(string $name, string $email): NewAccount
    {
        $password = Str::password(12, symbols: false);

        $user = User::create(['name' => $name, 'email' => $email, 'password' => $password]);
        $user->markEmailAsVerified(); // the owner vouches for the address

        return new NewAccount(new UserId($user->id), $password);
    }
}
