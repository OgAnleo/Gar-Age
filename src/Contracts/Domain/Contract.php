<?php

namespace GarAge\Contracts\Domain;

use DateTimeImmutable;
use GarAge\Tenancy\Domain\TenantId;
use LogicException;

/**
 * A customer renting a unit during a period at an agreed monthly price.
 */
final class Contract
{
    private function __construct(
        private readonly ?ContractId $id,
        public readonly TenantId $tenantId,
        private ContractDetails $details,
    ) {}

    /**
     * A new contract, not persisted yet (the repository assigns its id).
     */
    public static function create(TenantId $tenantId, ContractDetails $details): self
    {
        return new self(null, $tenantId, $details);
    }

    /**
     * A contract rebuilt from storage.
     */
    public static function restore(ContractId $id, TenantId $tenantId, ContractDetails $details): self
    {
        return new self($id, $tenantId, $details);
    }

    public function id(): ContractId
    {
        return $this->id ?? throw new LogicException('The contract has not been persisted yet.');
    }

    public function details(): ContractDetails
    {
        return $this->details;
    }

    public function changeDetails(ContractDetails $details): void
    {
        $this->details = $details;
    }

    public function statusOn(DateTimeImmutable $day): ContractStatus
    {
        $period = $this->details->period;

        return match (true) {
            $period->contains($day) => ContractStatus::Active,
            $day < $period->start => ContractStatus::Upcoming,
            default => ContractStatus::Finished,
        };
    }
}
