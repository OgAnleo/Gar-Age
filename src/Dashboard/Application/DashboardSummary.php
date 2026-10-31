<?php

namespace GarAge\Dashboard\Application;

use GarAge\Dashboard\Domain\FeeTally;
use GarAge\Dashboard\Domain\Occupancy;
use GarAge\Shared\Domain\Money;
use GarAge\Units\Domain\Unit;

/**
 * The figures of a business on a day: occupancy, revenue and unpaid fees.
 */
final readonly class DashboardSummary
{
    /**
     * @param  list<Unit>  $freeUnits  Ordered by code.
     * @param  non-empty-array<string, Money>  $collectedByMonth  "Y-m" => collected, oldest month first, ending with the current one.
     * @param  list<OverdueFee>  $overdueFees  Oldest due date first.
     */
    public function __construct(
        public Occupancy $occupancy,
        public array $freeUnits,
        public array $collectedByMonth,
        public Money $issuedThisMonth,
        public FeeTally $pending,
        public FeeTally $overdue,
        public array $overdueFees,
    ) {}

    public function collectedThisMonth(): Money
    {
        return $this->collectedByMonth[array_key_last($this->collectedByMonth)];
    }
}
