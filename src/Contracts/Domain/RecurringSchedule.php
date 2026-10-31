<?php

namespace GarAge\Contracts\Domain;

use DateTimeImmutable;
use GarAge\Pricing\Domain\BookingMode;
use GarAge\Pricing\Domain\PricingConditions;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Shared\Domain\Money;

/**
 * Booking by weeks, months or years from a start date, for a number of
 * periods or indefinitely (renewed every period until it is given an end).
 * Months and years keep the start day, clamped to short months
 * (from 31 Jan the next period starts on 28/29 Feb, then on 31 Mar).
 */
final readonly class RecurringSchedule extends Schedule
{
    public const int MAX_PERIODS = 120;

    public DateTimeImmutable $start;

    /**
     * @throws InvalidValue
     */
    public function __construct(
        private BookingMode $mode,
        DateTimeImmutable $start,
        public ?int $periods = null,
    ) {
        if (! $mode->isRecurring()) {
            throw new InvalidValue("The {$mode->value} mode is not recurring.");
        }

        if ($periods !== null && ($periods < 1 || $periods > self::MAX_PERIODS)) {
            throw new InvalidValue(sprintf('A booking lasts 1 to %d periods, %d given.', self::MAX_PERIODS, $periods));
        }

        $this->start = DateRange::day($start->format('Y-m-d'));
    }

    public function mode(): BookingMode
    {
        return $this->mode;
    }

    public function span(): DateRange
    {
        return DateRange::of($this->start, $this->periods === null ? null : $this->dayBefore($this->periodStart($this->periods)));
    }

    public function occupation(): array
    {
        return [$this->span()];
    }

    public function billingPeriodsUntil(DateTimeImmutable $day): array
    {
        $day = DateRange::day($day->format('Y-m-d'));
        $periods = [];

        for ($k = 0; ($this->periods === null || $k < $this->periods) && $this->periodStart($k) <= $day; $k++) {
            $periods[] = DateRange::of($this->periodStart($k), $this->dayBefore($this->periodStart($k + 1)));
        }

        return $periods;
    }

    public function priceWith(PricingConditions $conditions, Money $dailyRate): Money
    {
        return $conditions->periodPrice($dailyRate, $this->mode);
    }

    public function pricedLike(Schedule $other): bool
    {
        return $other instanceof self && $other->mode === $this->mode;
    }

    /**
     * First day of the k-th period (k = 0 is the start).
     */
    private function periodStart(int $k): DateTimeImmutable
    {
        if ($this->mode === BookingMode::Weekly) {
            return $this->start->modify('+'.(7 * $k).' days');
        }

        $months = $this->mode === BookingMode::Annual ? 12 * $k : $k;
        $month = $this->start->modify('first day of this month')->modify("+{$months} months");
        $day = min((int) $this->start->format('j'), (int) $month->format('t'));

        return $month->setDate((int) $month->format('Y'), (int) $month->format('n'), $day);
    }

    private function dayBefore(DateTimeImmutable $day): DateTimeImmutable
    {
        return $day->modify('-1 day');
    }
}
