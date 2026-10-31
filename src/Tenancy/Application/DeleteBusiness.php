<?php

namespace GarAge\Tenancy\Application;

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Domain\NotTenantOwner;
use GarAge\Tenancy\Domain\TenantEraser;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: an owner deletes their business with all of its data.
 */
final readonly class DeleteBusiness
{
    public function __construct(
        private RequireOwner $requireOwner,
        private TenantEraser $eraser,
    ) {}

    /**
     * @throws NotTenantOwner
     */
    public function handle(TenantId $tenantId, UserId $userId): void
    {
        $this->requireOwner->check($tenantId, $userId);

        $this->eraser->erase($tenantId);
    }
}
