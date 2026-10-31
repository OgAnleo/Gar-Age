<?php

namespace GarAge\Dashboard\Application;

use GarAge\Billing\Domain\Fee;
use GarAge\Contracts\Domain\Contract;

/**
 * An overdue fee with the contract it belongs to (null if it cannot be found).
 */
final readonly class OverdueFee
{
    public function __construct(
        public Fee $fee,
        public ?Contract $contract,
    ) {}
}
