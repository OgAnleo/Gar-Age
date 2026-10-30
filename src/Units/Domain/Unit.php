<?php

namespace GarAge\Units\Domain;

use GarAge\Tenancy\Domain\TenantId;
use LogicException;

/**
 * A storage room or garage that a tenant rents out.
 */
final class Unit
{
    private function __construct(
        private readonly ?UnitId $id,
        public readonly TenantId $tenantId,
        private UnitDetails $details,
    ) {}

    /**
     * A new unit, not persisted yet (the repository assigns its id).
     */
    public static function create(TenantId $tenantId, UnitDetails $details): self
    {
        return new self(null, $tenantId, $details);
    }

    /**
     * A unit rebuilt from storage.
     */
    public static function restore(UnitId $id, TenantId $tenantId, UnitDetails $details): self
    {
        return new self($id, $tenantId, $details);
    }

    public function id(): UnitId
    {
        return $this->id ?? throw new LogicException('The unit has not been persisted yet.');
    }

    public function details(): UnitDetails
    {
        return $this->details;
    }

    public function changeDetails(UnitDetails $details): void
    {
        $this->details = $details;
    }
}
