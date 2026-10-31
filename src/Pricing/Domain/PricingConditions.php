<?php

namespace GarAge\Pricing\Domain;

use GarAge\Shared\Domain\Money;
use LogicException;

/**
 * The economic conditions a business sets for each booking mode: a discount
 * on the daily rate for weekly, monthly and annual bookings. Specific days are
 * always charged at the plain daily rate.
 */
final readonly class PricingConditions
{
    public function __construct(
        public DiscountPercent $weekly,
        public DiscountPercent $monthly,
        public DiscountPercent $annual,
    ) {}

    public static function none(): self
    {
        return new self(new DiscountPercent(0), new DiscountPercent(0), new DiscountPercent(0));
    }

    public function discountFor(BookingMode $mode): DiscountPercent
    {
        return match ($mode) {
            BookingMode::Weekly => $this->weekly,
            BookingMode::Monthly => $this->monthly,
            BookingMode::Annual => $this->annual,
            BookingMode::Days => new DiscountPercent(0),
        };
    }

    /**
     * Price of one period of a recurring mode: daily rate × days of the period, minus its discount.
     */
    public function periodPrice(Money $dailyRate, BookingMode $mode): Money
    {
        $days = $mode->daysPerPeriod() ?? throw new LogicException('Specific days have no period price.');

        return $dailyRate->times($days)->percent(100 - $this->discountFor($mode)->value);
    }

    /**
     * Price of a number of specific days: the plain daily rate, no discount.
     */
    public function daysPrice(Money $dailyRate, int $days): Money
    {
        return $dailyRate->times($days);
    }
}
