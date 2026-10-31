<?php

namespace GarAge\Billing\Domain;

use DateTimeImmutable;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\InvalidValue;

/**
 * A calendar month ("2026-10"): the period a fee is charged for.
 */
final readonly class YearMonth
{
    public string $value;

    public function __construct(string $value)
    {
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value) !== 1) {
            throw new InvalidValue("\"{$value}\" is not a valid YYYY-MM month.");
        }

        $this->value = $value;
    }

    public static function containing(DateTimeImmutable $day): self
    {
        return new self($day->format('Y-m'));
    }

    /**
     * From the first to the last day of the month.
     */
    public function days(): DateRange
    {
        $first = new DateTimeImmutable("{$this->value}-01");

        return DateRange::fromStrings($first->format('Y-m-d'), $first->format('Y-m-t'));
    }

    public function equals(self $other): bool
    {
        return $other->value === $this->value;
    }
}
