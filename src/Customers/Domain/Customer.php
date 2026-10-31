<?php

namespace GarAge\Customers\Domain;

use GarAge\Tenancy\Domain\TenantId;
use LogicException;

/**
 * A person or company that rents units from a tenant.
 */
final class Customer
{
    private function __construct(
        private readonly ?CustomerId $id,
        public readonly TenantId $tenantId,
        private CustomerDetails $details,
    ) {}

    /**
     * A new customer, not persisted yet (the repository assigns its id).
     */
    public static function create(TenantId $tenantId, CustomerDetails $details): self
    {
        return new self(null, $tenantId, $details);
    }

    /**
     * A customer rebuilt from storage.
     */
    public static function restore(CustomerId $id, TenantId $tenantId, CustomerDetails $details): self
    {
        return new self($id, $tenantId, $details);
    }

    public function id(): CustomerId
    {
        return $this->id ?? throw new LogicException('The customer has not been persisted yet.');
    }

    public function details(): CustomerDetails
    {
        return $this->details;
    }

    public function changeDetails(CustomerDetails $details): void
    {
        $this->details = $details;
    }
}
