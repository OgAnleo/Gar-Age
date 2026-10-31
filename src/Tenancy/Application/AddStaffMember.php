<?php

namespace GarAge\Tenancy\Application;

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Domain\AlreadyMember;
use GarAge\Tenancy\Domain\NotTenantOwner;
use GarAge\Tenancy\Domain\Role;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Tenancy\Domain\TenantRepository;
use GarAge\Tenancy\Domain\UserDirectory;

/**
 * Use case: an owner adds an employee (staff) by email. Someone without an
 * account gets one with a temporary password, which is returned so the owner
 * can hand it over (the app sends no mail).
 */
final readonly class AddStaffMember
{
    public function __construct(
        private RequireOwner $requireOwner,
        private TenantRepository $tenants,
        private UserDirectory $users,
    ) {}

    /**
     * @return string|null The temporary password of a new account, null for an existing one.
     *
     * @throws NotTenantOwner
     * @throws AlreadyMember
     */
    public function handle(TenantId $tenantId, UserId $ownerId, string $name, string $email): ?string
    {
        $this->requireOwner->check($tenantId, $ownerId);

        $email = mb_strtolower(trim($email));
        $userId = $this->users->findByEmail($email);
        $temporaryPassword = null;

        if ($userId === null) {
            $account = $this->users->registerWithTemporaryPassword(trim($name), $email);
            [$userId, $temporaryPassword] = [$account->userId, $account->temporaryPassword];
        } elseif ($this->tenants->roleOf($tenantId, $userId) !== null) {
            throw AlreadyMember::of($tenantId, $userId);
        }

        $this->tenants->addMember($tenantId, $userId, Role::Staff);

        return $temporaryPassword;
    }
}
