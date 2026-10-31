<?php

namespace GarAge\Contracts\Domain;

use DateTimeImmutable;
use GarAge\Pricing\Domain\BookingMode;
use GarAge\Pricing\Domain\PricingConditions;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\Money;

/**
 * When a contract takes its unit and how it is billed, depending on its
 * booking mode. Each mode is a subclass, so a new mode adds a class instead
 * of new branches everywhere.
 */
abstract readonly class Schedule
{
    abstract public function mode(): BookingMode;

    /**
     * From the first to the last day covered (open-ended for indefinite bookings).
     */
    abstract public function span(): DateRange;

    /**
     * The days the unit is actually taken.
     *
     * @return list<DateRange>
     */
    abstract public function occupation(): array;

    /**
     * Billing periods that have started on or before the day; each one gets a fee.
     *
     * @return list<DateRange>
     */
    abstract public function billingPeriodsUntil(DateTimeImmutable $day): array;

    /**
     * Price of each billing period with the business conditions.
     */
    abstract public function priceWith(PricingConditions $conditions, Money $dailyRate): Money;

    /**
     * Whether changing to the other schedule can keep the agreed price
     * (same mode, and the same number of days when booking specific days).
     */
    abstract public function pricedLike(self $other): bool;

    public function overlaps(self $other): bool
    {
        if (! $this->span()->overlaps($other->span())) {
            return false;
        }

        foreach ($this->occupation() as $mine) {
            foreach ($other->occupation() as $theirs) {
                if ($mine->overlaps($theirs)) {
                    return true;
                }
            }
        }

        return false;
    }
}
