<?php

namespace GarAge\Contracts\Domain;

use DateTimeImmutable;
use GarAge\Pricing\Domain\BookingMode;
use GarAge\Pricing\Domain\PricingConditions;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Shared\Domain\Money;

/**
 * Booking of specific days, not necessarily consecutive. Charged once, at the
 * daily rate, when the first day arrives.
 */
final readonly class SpecificDaysSchedule extends Schedule
{
    public const int MAX_DAYS = 366;

    /** @var non-empty-list<DateTimeImmutable> Sorted, without repetitions. */
    public array $days;

    /**
     * @param  list<DateTimeImmutable>  $days
     *
     * @throws InvalidValue
     */
    public function __construct(array $days)
    {
        $unique = [];
        foreach ($days as $day) {
            $unique[$day->format('Y-m-d')] = DateRange::day($day->format('Y-m-d'));
        }
        ksort($unique);

        if ($unique === [] || count($unique) > self::MAX_DAYS) {
            throw new InvalidValue(sprintf('A booking of specific days needs 1 to %d days.', self::MAX_DAYS));
        }

        $this->days = array_values($unique);
    }

    /**
     * @param  list<string>  $days  "Y-m-d" dates.
     *
     * @throws InvalidValue
     */
    public static function fromStrings(array $days): self
    {
        return new self(array_map(DateRange::day(...), $days));
    }

    public function mode(): BookingMode
    {
        return BookingMode::Days;
    }

    public function span(): DateRange
    {
        return DateRange::of($this->days[0], $this->days[count($this->days) - 1]);
    }

    public function occupation(): array
    {
        return array_map(fn (DateTimeImmutable $day): DateRange => DateRange::of($day, $day), $this->days);
    }

    public function billingPeriodsUntil(DateTimeImmutable $day): array
    {
        return $this->span()->start <= DateRange::day($day->format('Y-m-d')) ? [$this->span()] : [];
    }

    public function priceWith(PricingConditions $conditions, Money $dailyRate): Money
    {
        return $conditions->daysPrice($dailyRate, count($this->days));
    }

    public function pricedLike(Schedule $other): bool
    {
        return $other instanceof self && count($other->days) === count($this->days);
    }

    /**
     * @return list<string> "Y-m-d" dates.
     */
    public function dayStrings(): array
    {
        return array_map(fn (DateTimeImmutable $day): string => $day->format('Y-m-d'), $this->days);
    }
}
