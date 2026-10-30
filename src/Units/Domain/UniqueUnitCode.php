<?php

namespace GarAge\Units\Domain;

use GarAge\Tenancy\Domain\TenantId;

/**
 * Domain service: a code can only be used once per tenant.
 */
final readonly class UniqueUnitCode
{
    public function __construct(private UnitRepository $units) {}

    /**
     * @throws UnitCodeAlreadyUsed
     */
    public function ensureAvailable(TenantId $tenantId, UnitCode $code, ?UnitId $except = null): void
    {
        if ($this->units->codeTaken($tenantId, $code, $except)) {
            throw UnitCodeAlreadyUsed::code($code);
        }
    }
}
